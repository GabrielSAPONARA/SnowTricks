import Filter from "../Filter.js";
import FilterMessage from "./FilterMessage.js";

new Filter(document.querySelector(".js-ajax"));


let figureLinks = Array.from(document.getElementsByClassName("js-figure-details"));
let figureModal = document.querySelector(".js-figure-informations");
let modalToEditPictureFigure = document.querySelector(".modal-picture-figure-to-edit");

figureLinks.forEach(link =>
{
    link.addEventListener("click", async (e) =>
    {
        e.preventDefault();
        try
        {
            figureModal.innerHTML = await fetchFigure(link.querySelector("h5").textContent)
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

            let editPictureButtons = document.querySelectorAll(".edit-picture");
            editPictureButtons.forEach(editPictureButton =>
            {
                editPictureButton.addEventListener("click", async (e) =>{
                    e.preventDefault();
                    let data = await fetchFormToEditPictureFigure(editPictureButton.id);
                    console.log(data);
                    let form = data.content;

                    console.log(form);

                    modalToEditPictureFigure.innerHTML = form;
                    openModal(e, editPictureButton)
                })

            })


        }
        catch (error)
        {
            console.log(error)
        }
        openModal(e, link)
    })
})

async function fetchFormToEditPictureFigure(pictureFigureId)
{
    let url = "http://localhost:8080/picture/figure/form/to/edit/" + pictureFigureId;
    const formResponse = await  fetch(url,
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
    if(formResponse.status >= 200 && formResponse.status < 300)
    {
        return await formResponse.json();
    }
}

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

const openModal = function (event, link)
{
    event.preventDefault();
    const target = document.querySelector(link.getAttribute("href"));
    target.showModal(); // Utilisez showModal() pour ouvrir le dialog
};

document.addEventListener('keydown', function (event)
{
    if (event.key === 'Escape' || event.key === 'Esc')
    {
        const dialogs = document.querySelector('dialogs[open]');
        for(let popup of dialogs)
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

