/**
 * Epistora Smooth Interaction Layer (v2.0)
 */

const Epistora = {
    /**
     * Handles specific reaction types (e.g., 'love', 'insight')
     * @param {string} postId - The unique ID of the post
     * @param {string} type - The type of reaction (key in JSON)
     * @param {string} elementId - The ID of the span to update
     */
    async react(postId, type, elementId) {
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('type', type); // Now sending the specific reaction key

        try {
            const response = await fetch('/epistora/actions/toggle_reaction.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Instantly update the specific counter in the UI
                const counter = document.getElementById(elementId);
                if (counter) {
                    counter.innerText = data.new_count;
                    
                    // Add a small visual "pop" effect
                    counter.parentElement.style.transform = "scale(1.2)";
                    setTimeout(() => {
                        counter.parentElement.style.transform = "scale(1)";
                    }, 150);
                }
            } else {
                console.error('Interaction Error:', data.error);
                if(data.error === 'Login required') alert("Please login to react.");
            }
        } catch (error) {
            console.error('Network Error:', error);
        }
    },

    /**
     * Toggle following status between users
     */
    async follow(writerId, btnElement) {
        const formData = new FormData();
        formData.append('writer_id', writerId);

        try {
            const response = await fetch('/epistora/actions/toggle_follow.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                btnElement.innerText = (data.action === 'followed') ? 'Unfollow' : 'Follow';
                btnElement.classList.toggle('following');
            }
        } catch (error) {
            console.error('Follow Action Failed:', error);
        }
    }
};