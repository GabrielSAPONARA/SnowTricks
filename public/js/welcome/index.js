let figureLinks = Array.from(document.getElementsByClassName("js-figure-details"));
let figureModal = document.getElementById("js-figure-informations");


figureLinks.forEach(link =>
{
    link.addEventListener("click", async (e) =>
    {
        e.preventDefault();
        console.log(link.querySelector("h5").textContent)
        try
        {
            figureModal.innerHTML = await fetchFigure(link.querySelector("h5").textContent)
            let saveMessageButton = document.getElementById("save-message");
            console.log(saveMessageButton);
            let messages = document.getElementById("messages");
            console.log(messages);


            saveMessageButton.addEventListener("click", async (e) =>
            {
                e.preventDefault();
                try
                {
                    let messageContent = document.getElementById("message_content").value;
                    let figureSlug =link.querySelector("h5").textContent;
                    messages.innerHTML = await fetchMessages(figureSlug, messageContent)
                }
                catch (error)
                {
                    console.error(error)
                }
            })

        }
        catch (error)
        {
            console.log(error)
        }
        console.log(link)
        openModal(e, link)
    })
})



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
        let data = await messageResponse.json();
        return data.content;
    }
    throw new Error("Failed to add the new message");
}

async function fetchFigure(figureSlug)
{
    let url = "http://localhost:8080/figure/" + figureSlug
    console.log(url)
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