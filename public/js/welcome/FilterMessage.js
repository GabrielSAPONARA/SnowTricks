/**
 * @property {HTMLElement} pagination
 * @property {HTMLElement} content
 * @property {number} page
 * @property {boolean} moreNav
 */
export default class Filter
{
    /**
     * @param {HTMLElement|null} element
     */
    constructor(element)
    {
        if(element === null || element === undefined)
        {
            return;
        }

        this.pagination = element.querySelector(".js-message-pagination");
        console.log(this.pagination);
        this.content = element.querySelector(".js-figure-messages");
        this.currentUrl = element.querySelector(".js-message-page").href;
        this.page = parseInt(new URLSearchParams(this.currentUrl.search).get("page") || 1);
        this.moreNav = this.page === 1;
        this.bindEvents();

    }

    bindEvents()
    {
        const aClickListener = e =>
        {
            if(e.target.tagName === 'A')
            {
                e.preventDefault();
                this.loadUrl(e.target.getAttribute("href"));
            }


        }
        if(this.moreNav)
        {
            this.pagination.innerHTML = "<button class='" +
                " btn" +
                " btn-primary'>See" +
                " more messages</button>";
            this.pagination.querySelector('button').addEventListener('click', this.loadMore.bind(this));
        }
        else
        {
            this.pagination.addEventListener("click", aClickListener)
        }
    }

    async loadMore()
    {
        const button = this.pagination.querySelector('button');
        button.setAttribute('disabled', 'disabled');
        this.page++;
        const url = this.currentUrl;
        const params = new URLSearchParams(url.search);
        params.set('page', this.page);
        await this.loadUrl(url + '?' + params.toString(), true);
        button.removeAttribute('disabled');
    }

    async loadUrl (url, append = false)
    {
        console.log(url);
        const params = new URLSearchParams(url.split('?')[1] || '')
        params.set('ajax', 1)
        const response = await fetch(url.split('?')[0] + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        if (response.status >= 200 && response.status < 300)
        {
            const data = await response.json()
            if(append)
            {
                this.content.innerHTML += data.content;
            }
            else
            {
                this.content.innerHTML = data.content;
            }
            if(!this.moreNav)
            {
                this.pagination.innerHTML = data.pagination;
            }
            else if (this.page === data.pages)
            {
                this.pagination.setAttribute('class', 'd-none');
            }
            else
            {
                this.pagination.style.display = null;
            }
            history.replaceState({}, '', url)
        }
        else
        {
            console.error("Failed to load url. " + response);
        }
    }
}