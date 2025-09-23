import Filter from "../Filter.js";
import FilterMessage from "./FilterMessage.js";

new Filter(document.querySelector(".js-ajax"));



let figureLinks = Array.from(document.getElementsByClassName("js-figure-details"));
let figureModal = document.querySelector(".js-figure-informations");

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
            let figureSlug =link.querySelector("h5").textContent;
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
            // pagination.addEventListener("click", async (e) =>
            // {
            //     if(e.target.tagName === 'A')
            //     {
            //         e.preventDefault();
            //         let url = e.target.href;
            //         let currentPage = pagination.querySelector("#currentPage").innerText;
            //         let data = await fecthMessagesAndPagination(url);
            //         messages.innerHTML = data.content;
            //         pagination.innerHTML = data.pagination;
            //     }
            // })
        }
        catch (error)
        {
            console.log(error)
        }
        openModal(e, link)
    })
})

// async function fecthMessagesAndPagination(url)
// {
//     const response = await fetch(url,
//         {
//             method: "POST",
//             headers:
//                 {
//                     'X-Requested-With': 'XMLHttpRequest',
//                 },
//             body: JSON.stringify(
//                 {
//                     // figureSlug: figureSlug,
//                     // currentPage: currentPage,
//                 })
//         })
//     if(response.status >= 200 && response.status < 300)
//     {
//         return await response.json();
//     }
// }

async function fetchMessages(figureSlug, messageContent)
{
    let url = "http://localhost:8080/message/new";
    const messageResponse = await  fetch(url, {
        method: "POST",
        headers:
            {
                'X-Requested-With': 'XMLHttpRequest',
            },
        body: JSON.stringify(
        {
                messageContent : messageContent,
                figureSlug : figureSlug,
            })
    });
    if(messageResponse.status >= 200 && messageResponse.status < 300)
    {
        return  await messageResponse.json();
    }
    throw new Error("Failed to add the new message");
}

async function fetchFigure(figureSlug)
{
    let url = "http://localhost:8080/figure/" + figureSlug
    const figureResponse = await fetch(url,{
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

const openModal = function (event, link) {
    event.preventDefault();
    const target = document.querySelector(link.getAttribute("href"));
    target.showModal(); // Utilisez showModal() pour ouvrir le dialog
};

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' || event.key === 'Esc') {
        const dialog = document.querySelector('dialog[open]');
        if (dialog) {
            dialog.close();
        }
    }
});

figureModal.addEventListener('click', function(event) {
    if (event.target === figureModal) {
        figureModal.close();
    }
});