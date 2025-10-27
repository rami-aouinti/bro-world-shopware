import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class AdvancedProductCustomizationPlugin extends Plugin {
    init() {
        this.form = DomAccess.querySelector(this.el, 'form', false);
        this.steps = Array.from(this.el.querySelectorAll('[data-customization-step]'));
        this.currentStepIndex = 0;
        this.optionButtons = Array.from(this.el.querySelectorAll('[data-customization-option]'));
        this.hiddenInput = DomAccess.querySelector(this.el, '[data-customization-input]', false);

        this.selectedOptions = [];

        if (this.form) {
            if (this.hiddenInput && this.hiddenInput.form !== this.form) {
                this.form.appendChild(this.hiddenInput);
            }
            this.registerEvents();
            this.updateStepVisibility();
        }
    }

    registerEvents() {
        this.optionButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const option = JSON.parse(button.getAttribute('data-customization-option'));
                this.toggleOption(option);
                this.persistSelection();
                button.classList.toggle('is-selected', this.isOptionSelected(option.id));
            });
        });

        this.el.querySelectorAll('[data-customization-next]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                this.nextStep();
            });
        });

        this.el.querySelectorAll('[data-customization-previous]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                this.previousStep();
            });
        });
    }

    toggleOption(option) {
        const existingIndex = this.selectedOptions.findIndex((item) => item.id === option.id);
        if (existingIndex >= 0) {
            this.selectedOptions.splice(existingIndex, 1);
        } else {
            this.selectedOptions.push(option);
        }
    }

    isOptionSelected(optionId) {
        return this.selectedOptions.some((item) => item.id === optionId);
    }

    persistSelection() {
        if (!this.hiddenInput) {
            return;
        }

        this.hiddenInput.value = JSON.stringify({
            options: this.selectedOptions,
        });
    }

    nextStep() {
        if (this.currentStepIndex < this.steps.length - 1) {
            this.currentStepIndex += 1;
            this.updateStepVisibility();
        }
    }

    previousStep() {
        if (this.currentStepIndex > 0) {
            this.currentStepIndex -= 1;
            this.updateStepVisibility();
        }
    }

    updateStepVisibility() {
        this.steps.forEach((step, index) => {
            step.classList.toggle('is-active', index === this.currentStepIndex);
        });

        const progress = DomAccess.querySelector(this.el, '[data-customization-progress]', false);
        if (progress) {
            progress.textContent = `${this.currentStepIndex + 1}/${this.steps.length}`;
        }
    }
}
