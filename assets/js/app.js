const grid = document.getElementById('postGrid');
const loader = document.getElementById('loader');
const toggle = document.getElementById('themeToggle');

let page = 1;
let loading = false;

/* ================= DARK MODE ================= */
if (
    localStorage.theme === 'dark' ||
    (!localStorage.theme && window.matchMedia('(prefers-color-scheme: dark)').matches)
) {
    document.documentElement.classList.add('dark');
}

toggle.onclick = () => {
    document.documentElement.classList.toggle('dark');
    localStorage.theme = document.documentElement.classList.contains('dark')
        ? 'dark'
        : 'light';
};

/* ================= SKELETON ================= */
function skeleton() {
    return `
        <div class="animate-pulse bg-white dark:bg-slate-800 p-5 rounded-xl shadow">
            <div class="h-4 w-20 bg-slate-200 dark:bg-slate-700 rounded mb-3"></div>
            <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded mb-2"></div>
            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded mb-4"></div>
            <div class="h-3 w-1/2 bg-slate-200 dark:bg-slate-700 rounded"></div>
        </div>
    `;
}

/* ================= LOAD POSTS ================= */
async function loadPosts() {
    if (loading) return;
    loading = true;

    loader.classList.remove('hidden');

    // skeletons
    for (let i = 0; i < 3; i++) grid.insertAdjacentHTML('beforeend', skeleton());

    const res = await fetch(`actions/load_posts.php?page=${page}`);
    const posts = await res.json();

    document.querySelectorAll('.animate-pulse').forEach(e => e.remove());

    posts.forEach(p => {
        grid.insertAdjacentHTML('beforeend', `
            <article class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow hover:-translate-y-1 transition">
                <span class="text-xs font-bold text-blue-600 uppercase">${p.tags?.[0] ?? 'General'}</span>
                <h2 class="font-semibold text-lg mt-2">
                    <a href="post/view/?id=${p.post_id}">${p.title}</a>
                </h2>
                <p class="text-sm text-slate-500 mt-2">${p.preview ?? ''}</p>
                <div class="flex justify-between mt-4 text-xs text-slate-400">
                    <span>${p.author}</span>
                    <span>👁 ${p.views ?? 0}</span>
                </div>
            </article>
        `);
    });

    if (posts.length) page++;
    loader.classList.add('hidden');
    loading = false;
}

/* ================= INFINITE SCROLL ================= */
window.addEventListener('scroll', () => {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 300) {
        loadPosts();
    }
});

/* INITIAL LOAD */
loadPosts();
