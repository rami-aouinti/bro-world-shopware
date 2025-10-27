import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class SmartInventoryAlert extends Plugin {
    init() {
        this.feedbackSuccess = DomAccess.querySelector(this.el, '.smart-inventory-alert__feedback', false);
        this.feedbackError = DomAccess.querySelector(this.el, '.smart-inventory-alert__feedback-error', false);
        this.el.addEventListener('submit', this.onSubmit.bind(this));
    }

    onSubmit(event) {
        event.preventDefault();

        const formData = new FormData(this.el);
        const action = this.el.getAttribute('action');

        fetch(action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Une erreur est survenue.');
                }
                this.showSuccess();
            })
            .catch((error) => {
                this.showError(error.message);
            });
    }

    showSuccess() {
        if (this.feedbackSuccess) {
            this.feedbackSuccess.classList.remove('d-none');
        }
        if (this.feedbackError) {
            this.feedbackError.classList.add('d-none');
        }
        this.el.reset();
    }

    showError(message) {
        if (this.feedbackError) {
            this.feedbackError.textContent = message;
            this.feedbackError.classList.remove('d-none');
        }
    }
}
