import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class AdvancedProductCustomizationPlugin extends Plugin {
    init() {
        this.form = DomAccess.querySelector(this.el, 'form', false);
        this.steps = Array.from(this.el.querySelectorAll('[data-customization-step]'));
        this.currentStepIndex = 0;
        this.optionButtons = Array.from(this.el.querySelectorAll('[data-customization-option]'));
        this.hiddenInput = DomAccess.querySelector(this.el, '[data-customization-input]', false);
        this.stepTriggers = Array.from(this.el.querySelectorAll('[data-customization-step-trigger]'));

        this.selectedOptions = [];
        this.stepSelections = new Map();

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
                const option = this.getOptionFromButton(button);

                if (!option) {
                    return;
                }

                const isSelected = this.toggleOption(option);
                this.persistSelection();
                button.classList.toggle('is-selected', isSelected);
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

        this.stepTriggers.forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                const targetIndex = Number.parseInt(trigger.getAttribute('data-step-index'), 10);

                if (Number.isNaN(targetIndex)) {
                    return;
                }

                if (!this.canNavigateTo(targetIndex)) {
                    return;
                }

                this.currentStepIndex = targetIndex;
                this.updateStepVisibility();
            });
        });
    }

    toggleOption(option) {
        const stepId = option.stepId || this.getActiveStepId();

        if (!stepId) {
            return false;
        }

        option.stepId = stepId;

        const existingIndex = this.selectedOptions.findIndex((item) => item.id === option.id && item.stepId === stepId);
        let isSelected;

        if (existingIndex >= 0) {
            this.selectedOptions.splice(existingIndex, 1);
            this.updateStepSelections(stepId, option.id, false);
            isSelected = false;
        } else {
            this.selectedOptions.push(option);
            this.updateStepSelections(stepId, option.id, true);
            isSelected = true;
        }

        this.updateStepVisibility();

        return isSelected;
    }

    updateStepSelections(stepId, optionId, isAdding) {
        if (!stepId) {
            return;
        }

        const existingSelections = this.stepSelections.get(stepId) || [];
        const selectionIndex = existingSelections.indexOf(optionId);

        if (isAdding) {
            if (selectionIndex === -1) {
                existingSelections.push(optionId);
            }
        } else if (selectionIndex >= 0) {
            existingSelections.splice(selectionIndex, 1);
        }

        if (existingSelections.length > 0) {
            this.stepSelections.set(stepId, existingSelections);
        } else {
            this.stepSelections.delete(stepId);
        }
    }

    isOptionSelected(optionId, stepId) {
        return this.selectedOptions.some((item) => item.id === optionId && item.stepId === stepId);
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
            step.classList.toggle('is-complete', this.isStepCompleted(index));
        });

        const progress = DomAccess.querySelector(this.el, '[data-customization-progress]', false);
        if (progress) {
            const activeStep = this.steps[this.currentStepIndex];
            const stepTitle = activeStep ? activeStep.getAttribute('data-step-title') : '';
            const stepCount = `${this.currentStepIndex + 1}/${this.steps.length}`;
            progress.textContent = stepTitle ? `${stepCount} – ${stepTitle}` : stepCount;
        }

        this.updateNavigationState();
        this.updateOptionButtonsState();
    }

    updateOptionButtonsState() {
        this.optionButtons.forEach((button) => {
            const option = this.getOptionFromButton(button);

            if (!option) {
                button.classList.remove('is-selected');
                return;
            }

            const isSelected = this.isOptionSelected(option.id, option.stepId);
            button.classList.toggle('is-selected', isSelected);
        });
    }

    updateNavigationState() {
        this.stepTriggers.forEach((trigger) => {
            const stepIndex = Number.parseInt(trigger.getAttribute('data-step-index'), 10);
            const isActive = stepIndex === this.currentStepIndex;
            const isComplete = this.isStepCompleted(stepIndex);
            const isAvailable = this.canNavigateTo(stepIndex);

            trigger.classList.toggle('is-active', isActive);
            trigger.classList.toggle('is-complete', isComplete);
            trigger.disabled = !isAvailable;
        });
    }

    isStepCompleted(stepIndex) {
        const step = this.steps[stepIndex];

        if (!step) {
            return false;
        }

        const stepId = step.getAttribute('data-step-id');
        const selections = this.stepSelections.get(stepId);

        return Array.isArray(selections) && selections.length > 0;
    }

    canNavigateTo(stepIndex) {
        if (stepIndex === this.currentStepIndex) {
            return true;
        }

        if (stepIndex < this.currentStepIndex) {
            return true;
        }

        if (stepIndex === this.currentStepIndex + 1) {
            return true;
        }

        return this.arePreviousStepsComplete(stepIndex);
    }

    arePreviousStepsComplete(targetIndex) {
        for (let index = 0; index < targetIndex; index += 1) {
            if (!this.isStepCompleted(index)) {
                return false;
            }
        }

        return true;
    }

    getActiveStepId() {
        const activeStep = this.steps[this.currentStepIndex];

        if (!activeStep) {
            return null;
        }

        return activeStep.getAttribute('data-step-id');
    }

    getOptionFromButton(button) {
        const rawOption = button.getAttribute('data-customization-option');

        if (!rawOption) {
            return null;
        }

        try {
            const option = JSON.parse(rawOption);
            const stepId = button.getAttribute('data-step-id') || button.closest('[data-customization-step]')?.getAttribute('data-step-id');

            if (stepId) {
                option.stepId = stepId;
            }

            return option;
        } catch (error) {
            console.warn('AdvancedProductCustomization: Unable to parse option data.', error);
        }

        return null;
    }
}
