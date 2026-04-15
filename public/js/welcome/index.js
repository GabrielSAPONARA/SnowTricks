import Filter from "../Filter.js";
import FilterMessage from "./FilterMessage.js";

// Initialize Filter on the static container
new Filter(document.querySelector(".js-ajax"));

// Select static elements that exist on load
let figureModal = document.querySelector(".js-figure-informations");
let modalToEditPictureFigure = document.querySelector(".modal-picture-figure-to-edit");
let popupToConfirmDeletion = document.getElementById("confirm-deletion");
let popupToConfirmPictureDeletion = document.getElementById("modal-picture-to-delete");
let popupToConfirmVideoDeletion = document.getElementById("modal-video-to-delete");

// --- EVENT DELEGATION FOR FIGURE LINKS ---
// Attach the listener to the static container '.js-ajax' instead of individual links
document.querySelector(".js-ajax").addEventListener("click", async (e) =>
{

    // Find the closest ancestor (or self) that matches the selector
    let link = e.target.closest(".js-figure-details");

    // If the click wasn't on a figure link, ignore it
    if (!link)
    {
        return;
    }

    e.preventDefault();

    try
    {
        let html = await fetchFigure(
            link.querySelector("p").textContent,
            link.querySelector("h5").textContent
        );

        // Clear modal content
        while (figureModal.firstChild)
        {
            figureModal.removeChild(figureModal.firstChild);
        }

        // Inject content after a short delay to ensure modal transition if needed
        setTimeout(() =>
        {
            figureModal.insertAdjacentHTML('afterbegin', html);

            // --- RE-ATTACH INTERNAL MODAL LISTENERS ---
            // Since the modal content is dynamic, these listeners must be re-added
            // every time the modal opens.

            let pencilToEditModal = document.getElementById("pencil-to-edit-figure");
            if (pencilToEditModal)
            {
                pencilToEditModal.addEventListener("click", async (e) =>
                {
                    e.preventDefault();
                    let html = await fetchFormToEditFigure(link.querySelector("h5").textContent);
                    while (figureModal.firstChild)
                    {
                        figureModal.removeChild(figureModal.firstChild);
                    }
                    figureModal.insertAdjacentHTML('afterbegin', html);

                    // Note: You will need to re-attach the inner edit/delete listeners
                    // here as well, similar to the logic below.
                    // For brevity, I've kept your existing logic structure inside the timeout.
                    // Ensure you wrap the inner logic in a function to avoid duplication.
                    initializeModalActions(link);
                });
            }

            // Initialize actions for the newly loaded content
            initializeModalActions(link);

        }, 100);

    }
    catch (error)
    {
        console.error(error);
    }

    // Open the modal
    openModal(e, link);
});

// Helper function to organize the complex inner logic
function initializeModalActions(link)
{
    let figureModal = document.querySelector(".js-figure-informations");
    let modalToEditPictureFigure = document.querySelector(".modal-picture-figure-to-edit");
    let figureSlug = link.querySelector("h5").textContent;
    let pagination = document.getElementById("pagination");
    let saveMessageButton = document.getElementById("save-message");
    let messages = document.getElementById("messages");

    // Edit Picture Logic
    let editPictureButtons = document.querySelectorAll(".edit-picture");
    editPictureButtons.forEach(editPictureButton =>
    {
        editPictureButton.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let data = await fetchFormToEditPictureFigure(editPictureButton.id);
            modalToEditPictureFigure.innerHTML = data.content;
            openModal(e, editPictureButton);

            let saveNewPicureButton = document.getElementById("save-new-picture");
            if (saveNewPicureButton)
            {
                saveNewPicureButton.addEventListener("click", async (e) =>
                {
                    e.preventDefault();
                    let fileInput = document.getElementById("picture_figure_form_image");
                    let filePicture = fileInput.files[0];
                    let formData = new FormData();
                    formData.append("picture_figure_form[image]", filePicture);
                    formData.append("picture_figure_form[_token]", document.getElementById("picture_figure_form__token").value);
                    await fetchFilePicture(formData, editPictureButton.id);
                    window.location.reload();
                });
            }
        });
    });

    // Edit Video Logic
    let editVideoPictures = document.querySelectorAll(".edit-video");
    editVideoPictures.forEach(editVideoButton =>
    {
        editVideoButton.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let data = await fetchFormToEditVideoFigure(editVideoButton.id);
            modalToEditPictureFigure.innerHTML = data.content;
            openModal(e, editVideoButton);

            let savedNewVideoButton = document.getElementById("save-new-video");
            if (savedNewVideoButton)
            {
                savedNewVideoButton.addEventListener("click", async (e) =>
                {
                    e.preventDefault();
                    let urlInput = document.getElementById("video_figure_form_embedUrl");
                    let urlToNewVideo = urlInput.value;
                    let formData = new FormData();
                    formData.append("video_figure_form[url]", urlToNewVideo);
                    formData.append("video_figure_form[_token]", document.getElementById("video_figure_form__token").value);
                    await fetchUrlVideo(formData, editVideoButton.id);
                    window.location.reload();
                });
            }
        });
    });

    // Delete Picture Logic
    let buttonsToDeletePicture = figureModal.querySelectorAll(".delete-picture");
    buttonsToDeletePicture.forEach(buttonToDeletePicture =>
    {
        buttonToDeletePicture.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let pictureId = buttonToDeletePicture.id;
            let pictureToken = buttonToDeletePicture.getAttribute("data-token");
            document.getElementById('modal-picture-to-delete').querySelector('a').setAttribute('data-picture-id', pictureId);
            document.getElementById('modal-picture-to-delete').querySelector('a').setAttribute('data-token', pictureToken);
            openModal(e, buttonToDeletePicture);
        });
    });

    // Delete Video Logic
    let buttonsToDeleteVideo = figureModal.querySelectorAll(".delete-video");
    buttonsToDeleteVideo.forEach(buttonToDeleteVideo =>
    {
        buttonToDeleteVideo.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let videoId = buttonToDeleteVideo.id; // Changed variable name for clarity
            let videoToken = buttonToDeleteVideo.getAttribute("data-token");
            document.getElementById('modal-video-to-delete').querySelector('a').setAttribute('data-video-id', videoId);
            document.getElementById('modal-video-to-delete').querySelector('a').setAttribute('data-token', videoToken);
            openModal(e, buttonToDeleteVideo);
        });
    });

    // Save Figure Change Logic
    let buttonToSaveFigureChange = document.getElementById('save-figure-change');
    if (buttonToSaveFigureChange)
    {
        buttonToSaveFigureChange.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let figureForm = document.querySelector("form");
            let formData = new FormData(figureForm);
            await updateFigure(figureSlug, formData);
            window.location.reload();
        });
    }

    // Delete Figure Logic (Inside Modal)
    let buttonToDeleteFigure = figureModal.getElementsByClassName("delete-figure-button");
    for (let deleteButtonIterator = 0; deleteButtonIterator < buttonToDeleteFigure.length; deleteButtonIterator++)
    {
        buttonToDeleteFigure[deleteButtonIterator].addEventListener("click", async (e) =>
        {
            e.preventDefault();
            document.getElementById('confirm-deletion').querySelector('a').setAttribute('data-figure-slug', figureSlug);
            openModal(e, buttonToDeleteFigure[deleteButtonIterator]);
        });
    }

    // Delete Figure Logic (Dustbin icon)
    let deleteFigureButton = document.getElementById('dustbin-to-delete-figure');
    if (deleteFigureButton)
    {
        deleteFigureButton.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            document.getElementById('confirm-deletion').querySelector('a').setAttribute('data-figure-slug', figureSlug);
            openModal(e, deleteFigureButton);
        });
    }

    // Message Logic
    if (saveMessageButton && messages && pagination)
    {
        // 1. Initialize FilterMessage for the initial state
        let filterMessageInstance = new FilterMessage(document.querySelector(".js-figure-informations"));

        // 2. Create/Error Container Setup (if it doesn't exist)
        let errorContainer = document.getElementById("message-error-container");
        if (!errorContainer)
        {
            errorContainer = document.createElement("div");
            errorContainer.id = "message-error-container";
            errorContainer.className = "col-12 text-danger mb-2 fw-bold small";

            const messageInput = document.getElementById("message_content");
            if (messageInput && messageInput.parentElement)
            {
                // Insert error message right before the input field
                messageInput.parentElement.insertBefore(errorContainer, messageInput);
            }
        }

        // 3. Add Message Listener
        saveMessageButton.addEventListener("click", async (e) =>
        {
            e.preventDefault();

            const messageInput = document.getElementById("message_content");
            if (!messageInput)
            {
                return;
            }

            let messageContent = messageInput.value.trim();

            // --- CLIENT-SIDE VALIDATION ---
            if (!messageContent)
            {
                errorContainer.textContent = "The message cannot be empty. Please write something.";
                messageInput.classList.add("is-invalid"); // Bootstrap red border

                // Remove error when user starts typing
                const clearError = () =>
                {
                    messageInput.classList.remove("is-invalid");
                    errorContainer.textContent = "";
                    messageInput.removeEventListener('input', clearError);
                };
                messageInput.addEventListener('input', clearError);
                return; // Stop execution, do NOT send request
            }

            // Clear previous errors
            errorContainer.textContent = "";
            messageInput.classList.remove("is-invalid");

            try
            {
                // --- SEND REQUEST ---
                let response = await fetchMessages(figureSlug, messageContent);

                // Check HTTP status (Handle 400 Bad Request from Server Validation)
                if (!response.ok)
                {
                    const errorData = await response.json();
                    throw new Error(errorData.error || "Failed to save message.");
                }

                const data = await response.json();

                // --- UPDATE DOM ---
                messages.innerHTML = data.content;
                pagination.innerHTML = data.pagination;

                // --- CRITICAL FIX: RE-INITIALIZE PAGINATION ---
                // Since we replaced the HTML, old listeners are gone. Create a new instance.
                const updatedContainer = document.querySelector(".js-figure-informations");
                if (updatedContainer)
                {
                    filterMessageInstance = new FilterMessage(updatedContainer);
                }

                // Clear Input
                messageInput.value = "";

            }
            catch (error)
            {
                console.error("Message error:", error);
                // Display Server or Network Error
                errorContainer.textContent = error.message;
                messageInput.classList.add("is-invalid");
            }
        });

        // NOTE: Manual pagination.addEventListener removed. FilterMessage handles it exclusively.
    }


    // Media Visibility Logic
    let buttonToSeeMedias = document.querySelector(".see-medias");
    let medias = document.querySelector(".medias");
    let buttonsToHideMedias = document.querySelectorAll(".hide-medias");

    if (buttonToSeeMedias && medias)
    {
        buttonToSeeMedias.addEventListener("click", (e) =>
        {
            e.preventDefault();
            medias.classList.remove("d-none");
            medias.classList.add("d-grid");
            buttonToSeeMedias.classList.remove("d-grid");
            buttonToSeeMedias.classList.add("d-none");
            buttonsToHideMedias.forEach(btn =>
            {
                btn.classList.remove("d-none");
                btn.classList.add("d-grid");
            });
        });

        for (let i = 0; i < buttonsToHideMedias.length; i++)
        {
            buttonsToHideMedias[i].addEventListener("click", (e) =>
            {
                e.preventDefault();
                buttonToSeeMedias.classList.remove("d-none");
                buttonToSeeMedias.classList.add("d-grid");
                medias.classList.remove("d-grid");
                medias.classList.add("d-none");
                for (let j = 0; j < buttonsToHideMedias.length; j++)
                {
                    buttonsToHideMedias[j].classList.remove("d-grid");
                    buttonsToHideMedias[j].classList.add("d-none");
                }
            });
        }
    }
}

// --- GLOBAL LISTENERS (Static elements) ---
// These remain largely the same as they target static IDs

document.addEventListener('keydown', function (event)
{
    if (event.key === 'Escape' || event.key === 'Esc')
    {
        // Fix selector: 'dialogs' -> 'dialog'
        const dialogs = document.querySelectorAll('dialog[open]');
        dialogs.forEach(popup =>
        {
            if (event.target === popup)
            {
                popup.close();
            }
        });
    }
});

figureModal.addEventListener('click', function (event)
{
    if (event.target === figureModal)
    {
        figureModal.close();
    }
});

if (modalToEditPictureFigure)
{
    modalToEditPictureFigure.addEventListener('click', function (event)
    {
        if (event.target === modalToEditPictureFigure)
        {
            modalToEditPictureFigure.close();
        }
    });
}

if (popupToConfirmDeletion)
{
    popupToConfirmDeletion.addEventListener('click', function (event)
    {
        if (event.target === popupToConfirmDeletion)
        {
            popupToConfirmDeletion.close();
        }
    });

    popupToConfirmDeletion.querySelectorAll("button").forEach(button =>
    {
        button.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            popupToConfirmDeletion.close();
        });
    });

    let confirmDeleteLink = popupToConfirmDeletion.querySelector("a");
    if (confirmDeleteLink)
    {
        confirmDeleteLink.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let dataToken = "";
            if (figureModal.open)
            {
                if (figureModal.querySelector("#dustbin-to-delete-figure") !== null)
                {
                    dataToken = figureModal.querySelector("#dustbin-to-delete-figure").getAttribute('data-token');
                }
                else
                {
                    const editDeleteBtn = figureModal.querySelector('#delete-button-in-modal-to-edit-figure');
                    if (editDeleteBtn)
                    {
                        dataToken = editDeleteBtn.getAttribute('data-token');
                    }
                }
            }
            else
            {
                dataToken = popupToConfirmDeletion.querySelector('a').getAttribute('data-token');
            }

            let slug = popupToConfirmDeletion.querySelector('a').getAttribute("data-figure-slug");
            if (slug && dataToken)
            {
                await deleteFigure(slug, dataToken);
                window.location.reload();
            }
        });
    }
}

if (popupToConfirmPictureDeletion)
{
    popupToConfirmPictureDeletion.addEventListener('click', function (event)
    {
        if (event.target === popupToConfirmPictureDeletion)
        {
            popupToConfirmPictureDeletion.close();
        }
    });

    popupToConfirmPictureDeletion.querySelectorAll("button").forEach(button =>
    {
        button.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            popupToConfirmPictureDeletion.close();
        });
    });

    let confirmPicLink = popupToConfirmPictureDeletion.querySelector("a");
    if (confirmPicLink)
    {
        confirmPicLink.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let dataToken = confirmPicLink.getAttribute("data-token");
            let pictureId = confirmPicLink.getAttribute("data-picture-id");
            if (pictureId && dataToken)
            {
                await deletePicture(pictureId, dataToken);
                window.location.reload();
            }
        });
    }
}

if (popupToConfirmVideoDeletion)
{
    popupToConfirmVideoDeletion.addEventListener('click', function (event)
    {
        if (event.target === popupToConfirmVideoDeletion)
        {
            popupToConfirmVideoDeletion.close();
        }
    });

    popupToConfirmVideoDeletion.querySelectorAll("button").forEach(button =>
    {
        button.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            popupToConfirmVideoDeletion.close();
        });
    });

    let confirmVidLink = popupToConfirmVideoDeletion.querySelector("a");
    if (confirmVidLink)
    {
        confirmVidLink.addEventListener("click", async (e) =>
        {
            e.preventDefault();
            let dataToken = confirmVidLink.getAttribute("data-token");
            let videoId = confirmVidLink.getAttribute("data-video-id");
            if (videoId && dataToken)
            {
                await deleteVideo(videoId, dataToken);
                window.location.reload();
            }
        });
    }
}

// --- API FUNCTIONS (Unchanged) ---
// Keep all your async fetch functions (deletePicture, deleteVideo, fetchFigure, etc.) exactly as they were.
// They are correctly defined.

async function deletePicture(pictureId, token)
{
    let url = "http://localhost:8080/picture/figure/delete/" + pictureId;
    const response = await fetch(url, {
        method: "DELETE",
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        }
    });
    if (response.ok)
    {
        return await response.json();
    }
    else
    {
        throw new Error(`HTTP error: ${response.status}`);
    }
}

async function deleteVideo(videoId, token)
{
    let url = "http://localhost:8080/video/figure/delete/" + videoId;
    const response = await fetch(url, {
        method: "DELETE",
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        }
    });
    if (response.ok)
    {
        return await response.json();
    }
    else
    {
        throw new Error(`HTTP error: ${response.status}`);
    }
}

async function deleteFigure(figureSlug, token)
{
    let url = "http://localhost:8080/figure/delete/" + figureSlug;
    const response = await fetch(url, {
        method: "DELETE",
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({figureSlug: figureSlug})
    });
    if (response.ok)
    {
        return await response.json();
    }
    else
    {
        throw new Error(`HTTP error: ${response.status}`);
    }
}

async function updateFigure(figureSlug, formData)
{
    let url = "http://localhost:8080/figure/update/" + figureSlug;
    formData.append('figureSlug', figureSlug);
    const response = await fetch(url, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData,
    });
    if (response.status >= 200 && response.status < 300)
    {
        return await response.json();
    }
}

async function fetchFormToEditFigure(figureSlug)
{
    let url = "http://localhost:8080/figure/edit/modal/" + figureSlug;
    const figureResponse = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
    if (figureResponse.status >= 200 && figureResponse.status < 300)
    {
        let data = await figureResponse.json();
        return data.content;
    }
    throw new Error("Failed to fetch figure " + figureSlug);
}

async function fetchUrlVideo(formData, videoFigureId)
{
    let url = "http://localhost:8080/video/figure/edit/" + videoFigureId;
    const response = await fetch(url, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData
    });
    if (response.status >= 200 && response.status < 300)
    {
        return await response.json();
    }
}

async function fetchFormToEditVideoFigure(videoFigureId)
{
    let url = "http://localhost:8080/video/figure/form/to/edit/" + videoFigureId;
    const formResponse = await fetch(url, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({id: videoFigureId})
    });
    if (formResponse.status >= 200 && formResponse.status < 300)
    {
        return await formResponse.json();
    }
}

async function fetchFilePicture(formData, pictureFigureId)
{
    let url = "http://localhost:8080/picture/figure/edit/" + pictureFigureId;
    const response = await fetch(url, {
        method: "POST",
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData
    });
    if (response.status >= 200 && response.status < 300)
    {
        return await response.json();
    }
}

async function fetchFormToEditPictureFigure(pictureFigureId)
{
    let url = "http://localhost:8080/picture/figure/form/to/edit/" + pictureFigureId;
    const formResponse = await fetch(url, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({id: pictureFigureId})
    });
    if (formResponse.status >= 200 && formResponse.status < 300)
    {
        return await formResponse.json();
    }
}

async function fecthMessagesAndPagination(url)
{
    const response = await fetch(url, {
        method: "POST",
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({})
    });
    if (response.status >= 200 && response.status < 300)
    {
        return await response.json();
    }
}

async function fetchMessages(figureSlug, messageContent)
{
    let url = "http://localhost:8080/message/new";

    // FIX: Target the form specifically inside the modal or near the message input
    // Instead of querying the whole document, look relative to the input or the modal
    const messageInput = document.getElementById("message_content");
    const form = messageInput ? messageInput.closest('form') : document.querySelector('form');

    // Look specifically for the token with name="message[_token]"
    let tokenInput = form ? form.querySelector('input[name="message[_token]"]') : null;

    // Fallback if not found in the specific form
    if (!tokenInput)
    {
        tokenInput = document.querySelector('input[id="message__token"]');
    }

    let csrfToken = tokenInput ? tokenInput.value : '';

    console.log("Token found:", csrfToken ? "YES" : "NO");
    if (csrfToken)
    {
        console.log("Token Start:", csrfToken.substring(0, 15));
    }

    if (!csrfToken)
    {
        console.error("CSRF Token not found!");
        throw new Error("Security token missing.");
    }

    const response = await fetch(url, {
        method: "POST",
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            messageContent: messageContent,
            figureSlug: figureSlug
        })
    });

    if (response.status >= 200 && response.status < 300)
    {
        return response;
    }

    return response; // Return error response to be handled by catch
}

async function fetchFigure(figureGroup, figureSlug)
{
    let url = "http://localhost:8080/figure/" + figureGroup + "/" + figureSlug;
    const figureResponse = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
    if (figureResponse.status >= 200 && figureResponse.status < 300)
    {
        let data = await figureResponse.json();
        return data.content;
    }
    throw new Error("Failed to fetch figure " + figureSlug);
}

const openModal = function (event, link)
{
    event.preventDefault();
    const target = document.querySelector(link.getAttribute("data-modal"));
    if (target)
    {
        target.showModal();
    }
};