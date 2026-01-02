let page = 1;
let loading = false;

const feed = document.getElementById('feed');
const loader = document.getElementById('skeletons');

async function loadMore() {
    if (loading) return;
    loading = true;
    loader.classList.remove('hidden');

    page++;

    const res = await fetch(`/actions/load_posts.php?page=${page}`);
    const html = await res.text();

    if (html.trim()) {
        feed.insertAdjacentHTML('beforeend', html);
    }

    loader.classList.add('hidden');
    loading = false;
}

window.addEventListener('scroll', () => {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
        loadMore();
    }
});
