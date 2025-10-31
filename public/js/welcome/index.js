import Filter from "../Filter.js";
import FilterMessage from "./FilterMessage.js";

new Filter(document.querySelector(".js-ajax"));


let figureLinks = Array.from(document.getElementsByClassName("js-figure-details"));
let figureModal = document.querySelector(".js-figure-informations");
let modalToEditPictureFigure = document.querySelector(".modal-picture-figure-to-edit");
let popupToConfirmDeletion = document.getElementById("confirm-deletion");

figureLinks.forEach(link =>
{
    link.addEventListener("click", async (e) =>
    {
        e.preventDefault();
        try
        {
            let html = await fetchFigure(link.querySelector("h5").textContent);

            // Vider le modal proprement sans casser le DOM
            while (figureModal.firstChild)
            {
                figureModal.removeChild(figureModal.firstChild);
            }

            // Attendre que le modal soit ouvert avant d'injecter le HTML
            setTimeout(() =>
            {
                figureModal.insertAdjacentHTML('afterbegin', html);

                let pencilToEditModal = document.getElementById("pencil-to-edit-figure");
                console.log(pencilToEditModal);

                pencilToEditModal.addEventListener("click", async (e) =>
                {
                    e.preventDefault();
                    html = await fetchFormToEditFigure(link.querySelector("h5").textContent);
                    console.log(html);
                    while (figureModal.firstChild)
                    {
                        figureModal.removeChild(figureModal.firstChild);
                    }
                    figureModal.insertAdjacentHTML('afterbegin', html);

                    let editPictureButtons = document.querySelectorAll(".edit-picture");
                    editPictureButtons.forEach(editPictureButton =>
                    {
                        editPictureButton.addEventListener("click", async (e) =>
                        {
                            e.preventDefault();
                            let data = await fetchFormToEditPictureFigure(editPictureButton.id);
                            modalToEditPictureFigure.innerHTML = data.content;
                            openModal(e, editPictureButton)

                            let saveNewPicureButton = document.getElementById("save-new-picture");
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
                            })
                        })
                    })
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
                            })
                        })
                    })

                    let buttonToSaveFigureChange = document.getElementById('save-figure-change');
                    buttonToSaveFigureChange.addEventListener("click", async (e) =>
                    {
                        e.preventDefault();
                        let figureForm = document.querySelector("form")
                        let formData = new FormData(figureForm);

                        await updateFigure(figureSlug, formData);
                        window.location.reload();
                    })

                    let buttonToDeleteFigure = figureModal.getElementsByClassName("delete-figure-button");

                    for(let deleteButtonIterator = 0; deleteButtonIterator < buttonToDeleteFigure.length; deleteButtonIterator++)
                    {
                        buttonToDeleteFigure[deleteButtonIterator].addEventListener("click", async (e) => {
                            e.preventDefault();
                            document.getElementById('confirm-deletion').querySelector('a').setAttribute('data-figure-slug',figureSlug);
                            openModal(e, buttonToDeleteFigure[deleteButtonIterator]);
                        })
                    }
                })

                let deleteFigureButton = document.getElementById('dustbin-to-delete-figure');

                deleteFigureButton.addEventListener("click", async (e) =>
                {
                    e.preventDefault();
                    document.getElementById('confirm-deletion').querySelector('a').setAttribute('data-figure-slug',figureSlug);
                    openModal(e, deleteFigureButton);


                })

                let saveMessageButton = document.getElementById("save-message");
                let messages = document.getElementById("messages");
                let figureSlug = link.querySelector("h5").textContent;
                let pagination = document.getElementById("pagination");
                saveMessageButton.addEventListener("click", async (e) =>
                {
                    e.preventDefault();
                    try
                    {
                        let messageContent = document.getElementById("message_content").value;
                        let data = await fetchMessages(figureSlug, messageContent)
                        messages.innerHTML = data.content;
                        pagination.innerHTML = data.pagination;
                    }
                    catch (error)
                    {
                        console.error(error)
                    }
                })

                new FilterMessage(document.querySelector(".js-figure-informations"));
                pagination.addEventListener("click", async (e) =>
                {
                    if (e.target.tagName === 'A')
                    {
                        e.preventDefault();
                        let url = e.target.href;
                        let currentPage = pagination.querySelector("#currentPage").innerText;
                        let data = await fecthMessagesAndPagination(url);
                        messages.innerHTML = data.content;
                        pagination.innerHTML = data.pagination;
                    }
                })


            }, 100);

        }
        catch (error)
        {
            console.log(error)
        }
        openModal(e, link)



    })
})




async function deleteFigure(figureSlug, token)
{
    console.log(figureSlug);
    console.log(token);
    let url = "http://localhost:8080/figure/delete/" + figureSlug;
    const response = await fetch(url, {
        method: "DELETE",
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({figureSlug: figureSlug})
    })
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
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
    });
    if (response.status >= 200 && response.status < 300)
    {
        let data = await response.json()
        return data.content;
    }
}

async function fetchFormToEditFigure(figureSlug)
{
    let url = "http://localhost:8080/figure/edit/modal/" + figureSlug
    const figureResponse = await fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    });
    if (figureResponse.status >= 200 && figureResponse.status < 300)
    {
        let data = await figureResponse.json()
        return data.content

    }
    throw new Error("Failed to fetch figure" + figureSlug);
}

async function fetchUrlVideo(formData, videoFigureId)
{
    let url = "http://localhost:8080/video/figure/edit/" + videoFigureId;
    const response = await fetch(url,
        {
            method: 'POST',
            headers:
                {
                    'X-Requested-With': 'XMLHttpRequest',
                },
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
    const formResponse = await fetch(url,
        {
            method: 'POST',
            headers:
                {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            body: JSON.stringify({
                id: videoFigureId,
            })

        })
    if (formResponse.status >= 200 && formResponse.status < 300)
    {
        return await formResponse.json();
    }
}

/**
 *
 * @param formData
 * @param pictureFigureId
 * @returns {Promise<any>}
 */
async function fetchFilePicture(formData, pictureFigureId)
{
    let url = "http://localhost:8080/picture/figure/edit/" + pictureFigureId;
    const response = await fetch(url,
        {
            method: "POST",
            headers:
                {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            body: formData
        })
    if (response.status >= 200 && response.status < 300)
    {
        return await response.json()

    }
}

/**
 * @param pictureFigureId
 * @returns {Promise<any>}
 */
async function fetchFormToEditPictureFigure(pictureFigureId)
{
    let url = "http://localhost:8080/picture/figure/form/to/edit/" + pictureFigureId;
    const formResponse = await fetch(url,
        {
            method: 'POST',
            headers:
                {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            body: JSON.stringify({
                id: pictureFigureId,
            })

        })
    if (formResponse.status >= 200 && formResponse.status < 300)
    {
        return await formResponse.json();
    }
}

/**
 * @param url
 * @returns {Promise<any>}
 */
async function fecthMessagesAndPagination(url)
{
    const response = await fetch(url, {
        method: "POST", headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }, body: JSON.stringify({
            // figureSlug: figureSlug,
            // currentPage: currentPage,
        })
    })
    if (response.status >= 200 && response.status < 300)
    {
        return await response.json();
    }
}

/**
 * @param figureSlug
 * @param messageContent
 * @returns {Promise<any>}
 */
async function fetchMessages(figureSlug, messageContent)
{
    let url = "http://localhost:8080/message/new";
    const messageResponse = await fetch(url, {
        method: "POST", headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }, body: JSON.stringify({
            messageContent: messageContent, figureSlug: figureSlug,
        })
    });
    if (messageResponse.status >= 200 && messageResponse.status < 300)
    {
        return await messageResponse.json();
    }
    throw new Error("Failed to add the new message");
}

/**
 * @param figureSlug
 * @returns {Promise<*>}
 */
async function fetchFigure(figureSlug)
{
    let url = "http://localhost:8080/figure/" + figureSlug
    const figureResponse = await fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    });
    if (figureResponse.status >= 200 && figureResponse.status < 300)
    {
        let data = await figureResponse.json()
        return data.content

    }
    throw new Error("Failed to fetch figure" + figureSlug);
}

/**
 * @param event
 * @param link
 */
const openModal = function (event, link)
{
    event.preventDefault();
    const target = document.querySelector(link.getAttribute("href"));
    console.log(link);
    target.showModal(); // Utilisez showModal() pour ouvrir le dialog
};

document.addEventListener('keydown', function (event)
{
    if (event.key === 'Escape' || event.key === 'Esc')
    {
        const dialogs = document.querySelector('dialogs[open]');
        for (let popup of dialogs)
        {
            if (event.target === popup)
            {
                popup.close();
            }
        }
    }
});

figureModal.addEventListener('click', function (event)
{
    if (event.target === figureModal)
    {
        figureModal.close();
    }
});
modalToEditPictureFigure.addEventListener('click', function (event)
{
    if (event.target === modalToEditPictureFigure)
    {
        modalToEditPictureFigure.close();
    }
});

popupToConfirmDeletion.addEventListener('click', function (event)
{
    if (event.target === popupToConfirmDeletion)
    {
        popupToConfirmDeletion.close();
    }
})

popupToConfirmDeletion.querySelectorAll("button").forEach(button =>
{
    button.addEventListener("click", async (e) => {
        e.preventDefault();
        popupToConfirmDeletion.close();
    })
})

popupToConfirmDeletion.querySelector("a").addEventListener("click", async (e) =>{
    e.preventDefault();
    let dataToken = "";
    if(figureModal.open)
    {
        console.log(figureModal);
        if(figureModal.querySelector("#dustbin-to-delete-figure") !== null)
        {
            dataToken = figureModal.querySelector("#dustbin-to-delete-figure").getAttribute('data-token');
        }
        else
        {
            dataToken = figureModal.querySelector('#delete-button-in-modal-to-edit-figure').getAttribute('data-token');
        }
    }
    else
    {
        dataToken = popupToConfirmDeletion.querySelector('a').getAttribute('data-token');
    }
    console.log(dataToken);
    let response = await deleteFigure(popupToConfirmDeletion.querySelector('a').getAttribute("data-figure-slug"), dataToken);
    window.location.reload();
})