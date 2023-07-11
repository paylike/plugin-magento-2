require([
    'jquery'
],function(
    Jquery
    ) {

    Jquery(document).ajaxComplete(() => {

        if (Jquery(".aw-onestep-sidebar-wrapper button.action.primary.checkout").length > 0) {

            if (Jquery('#paylikepaymentmethod').is(":checked")) {
                addEvenTriggerOnPrimaryButton();
                return;
            }

            Jquery('#paylikepaymentmethod').on('click', () => {
                addEvenTriggerOnPrimaryButton();
            });

        }

        function addEvenTriggerOnPrimaryButton() {
            Jquery(".aw-onestep-sidebar-wrapper button.action.primary.checkout")
            .off("click")
            .click((e) => {
                    e.preventDefault();
                    Jquery('#paylikepaymentmethod-button').trigger('click');
            });
        }
    });
});