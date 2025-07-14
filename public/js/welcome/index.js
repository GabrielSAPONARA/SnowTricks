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

        }
        catch (error)
        {
            console.log(error)
        }
        console.log(link)
        openModal(e, link)
    })
})

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