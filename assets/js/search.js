/**
 * Epistora Real-Time Search Engine
 */

const SearchEngine = {
    timeout: null,

    init() {
        const input = document.querySelector('input[name="q"]');
        const resultsBox = document.createElement('div');
        resultsBox.id = 'search-suggestions';
        input.parentNode.appendChild(resultsBox);

        input.addEventListener('input', (e) => {
            clearTimeout(this.timeout);
            const query = e.target.value;

            if (query.length < 2) {
                resultsBox.innerHTML = '';
                return;
            }

            // Debounce: Wait 300ms after user stops typing
            this.timeout = setTimeout(() => this.fetchResults(query, resultsBox), 300);
        });
    },

    async fetchResults(query, container) {
        try {
            const response = await fetch(`/actions/search_suggest.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.length > 0) {
                container.innerHTML = data.map(item => `
                    <div class="suggestion-item" onclick="location.href='/post/view/?id=${item.id}'">
                        <span class="type-badge">${item.type}</span>
                        <span class="title-text">${item.title}</span>
                    </div>
                `).join('');
                container.style.display = 'block';
            } else {
                container.innerHTML = '';
            }
        } catch (err) {
            console.error("Search failed", err);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => SearchEngine.init());