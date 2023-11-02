// Restricts input for the set of matched elements to the given inputFilter function.
(function ($) {
  $.fn.inputFilter = function (callback, errMsg) {
    return this.on(
      "input keydown keyup mousedown mouseup select contextmenu drop focusout",
      function (e) {
        if (callback(this.value)) {
          // Accepted value
          if (["keydown", "mousedown", "focusout"].indexOf(e.type) >= 0) {
            $(this).removeClass("input-error");
            this.setCustomValidity("");
          }
          this.oldValue = this.value;
          this.oldSelectionStart = this.selectionStart;
          this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
          // Rejected value - restore the previous one
          $(this).addClass("input-error");
          this.setCustomValidity(errMsg);
          this.reportValidity();
          this.value = this.oldValue;
          this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        } else {
          // Rejected value - nothing to restore
          // this.value = "";
        }
      }
    );
  };
})(jQuery);

function validateEmail($email) {
  var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
  return emailReg.test($email);
}

var modal = jQuery(document).find("#skyfinityqc_checkout_modal");
const body = document.querySelector("body");
jQuery(document).on("click", "#skyfinityqc_checkout_button", function () {
  jQuery(modal).addClass("show");
  navigateToFormStep("1");
  body.style.overflow = "hidden";
  skyfinityqc_get_cart_contents();
  refresh_payment_list();
  refresh_coupons_list();
});

jQuery(document).on("click", "#skyfinityqc_checkout_modal .close", function () {
  jQuery('.confirm-close-quick-checkout').addClass('show');
});
jQuery(document).on('click', '.confirm-close-quick-checkout .confirm-no', function () {
  jQuery('.confirm-close-quick-checkout').removeClass('show');
});
jQuery(document).on('click', '.confirm-close-quick-checkout .confirm-yes', function () {
  jQuery('.confirm-close-quick-checkout').removeClass('show');
  jQuery(modal).removeClass("show");
  body.style.overflow = "auto";
});
/**
 * Define a function to navigate betweens form steps.
 * It accepts one parameter. That is - step number.
 */
var navigateToFormStep = (stepNumber, validate = false) => {
  let currentStep = jQuery("#skyfinity-quick-step-form-container").attr(
    "data-currentStep"
  );

  /**
   * Run Validation for the step
   */
  if (validate) {
    let validFlag = true;

    if (currentStep === "1") {
      let mobileInput = jQuery("#step-1").find('input[name="mobile"]');
      let mobile = jQuery(mobileInput).val();
      if (mobile === "") {
        jQuery(mobileInput).addClass("error");
        validFlag = false;
      } else {
        jQuery(mobileInput).removeClass("error");
      }

      let emailInput = jQuery("#step-1").find('input[name="email"]');
      let email = jQuery(emailInput).val();
      if (email === "") {
        jQuery(emailInput).addClass("error");
        validFlag = false;
      } else {
        jQuery(emailInput).removeClass("error");
      }
    } else if (currentStep === "2") {
      let selectedAddress = jQuery('input[name="shipping_address"]:checked');
      if (selectedAddress.length <= 0) {
        validFlag = false;
        jQuery('input[name="shipping_address"]')
          .closest(".address-item")
          .addClass("error");
          jQuery("#address_error_message").text("Please Select Address");
      } else {
        jQuery('input[name="shipping_address"]')
          .closest(".address-item")
          .removeClass("error");
        let id = jQuery(selectedAddress).val();
        setShippingAddress(id);
      }
    } else if (currentStep === "3") {
      let selectedPayment = jQuery('input[name="payment_method"]:checked');
      if (selectedPayment.length <= 0) {
        validFlag = false;
        // jQuery('input[name="payment_method"]')
        //   .closest("label")
        //   .addClass("error");
        // jQuery("#payment-method-error").text("Please Select Payment Method");
      } else {
        jQuery('input[name="payment_method"]')
          .closest("label")
          .removeClass("error");
        next_action_for_payment_method();
      }
    }
    if (!validFlag) {
      return false;
    }
  }

  if (document.querySelector("#step-" + stepNumber))
    jQuery("#skyfinity-quick-step-form-container").attr(
      "data-currentStep",
      stepNumber
    );
  if (stepNumber === 2) {
    refresh_address_list &&
      refresh_address_list(function () {
        jQuery(".address-list")
          .find(".address-item")
          .eq(0)
          .find('input[type="radio"]')
          .prop("checked", true);
        checkSelectedAddress();
      });
  }
  if (stepNumber === 3) {
    jQuery("#payment-method-error").text("");
    refresh_payment_list && refresh_payment_list();
  }
  /**
   * Hide all form steps.
   */
  if (document.querySelector("#step-" + stepNumber))
    document.querySelectorAll(".form-step").forEach((formStepElement) => {
      formStepElement.classList.add("d-none");
    });
  /**
   * Mark all form steps as unfinished.
   */
  document.querySelectorAll(".form-stepper-list").forEach((formStepHeader) => {
    formStepHeader.classList.add("form-stepper-unfinished");
    formStepHeader.classList.remove(
      "form-stepper-active",
      "form-stepper-completed"
    );
  });
  /**
   * Show the current form step (as passed to the function).
   */
  if (document.querySelector("#step-" + stepNumber))
    document.querySelector("#step-" + stepNumber).classList.remove("d-none");
  /**
   * Select the form step circle (progress bar).
   */
  const formStepCircle = document.querySelector(
    'li[step="' + stepNumber + '"]'
  );
  /**
   * Mark the current form step as active.
   */
  if (formStepCircle && formStepCircle.classList) {
    formStepCircle.classList.remove(
      "form-stepper-unfinished",
      "form-stepper-completed"
    );
    formStepCircle.classList.add("form-stepper-active");
  }
  /**
   * Loop through each form step circles.
   * This loop will continue up to the current step number.
   * Example: If the current step is 3,
   * then the loop will perform operations for step 1 and 2.
   */
  for (let index = 0; index < stepNumber; index++) {
    /**
     * Select the form step circle (progress bar).
     */
    const formStepCircle = document.querySelector('li[step="' + index + '"]');
    /**
     * Check if the element exist. If yes, then proceed.
     */
    if (formStepCircle) {
      /**
       * Mark the form step as completed.
       */
      formStepCircle.classList.remove(
        "form-stepper-unfinished",
        "form-stepper-active"
      );
      formStepCircle.classList.add("form-stepper-completed");
    }
  }
};

// checkout Instructions script
function toggleInstructions() {
  const label = document.getElementById('instructions-label');
  const input = document.getElementById('instruction');

  if (input.style.display === 'none') {
      // Show the input field and change the label text
      input.style.display = 'block';
      label.textContent = 'Instructions for your order';
  } else {
      // Hide the input field and change the label text
      input.style.display = 'none';
      label.textContent = '+Instructions for your order';
  }
}

/**
 * Select all form navigation buttons, and loop through them.
 */
function refresh_button_action() {
  document
    .querySelectorAll(".btn-navigate-form-step")
    .forEach((formNavigationBtn) => {
      /**
       * Add a click event listener to the button.
       */
      formNavigationBtn.addEventListener("click", () => {
        /**
         * Get the value of the step.
         */
        const stepNumber = parseInt(
          formNavigationBtn.getAttribute("step_number")
        );
        /**
         * Call the function to navigate to the target form step.
         */
        const is_next_btn_clicked = formNavigationBtn.getAttribute("data-next");
        navigateToFormStep(
          stepNumber,
          is_next_btn_clicked ? is_next_btn_clicked : false
        );
      });
    });
}
refresh_button_action();

// // Get the modal
// var modal = document.getElementById("skyfinityqc_checkout_modal");

// // Get the button that opens the modal
// var btn = document.getElementById("myBtn");

// // Get the <span> element that closes the modal
// var span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal
// btn.onclick = function() {
//   modal.style.display = "block";
// }

// // When the user clicks on <span> (x), close the modal
// span.onclick = function() {
//   modal.style.display = "none";
// }

// When the user clicks anywhere outside of the modal, close it
// window.onclick = function(event) {
//   if (event.target == modal) {
//     modal.style.display = "none";
//   }
// }

// skyfinity-quick-popup-address file
function setShippingAddress(id) {
    jQuery('#address_error_message').text('');
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: request_globals.request_url,
        data: {
            action: "skyfinityqc_set_address_to_checkout",
            id: id
        },
        success: function(response) {
            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            if (response.success == true) {
                // 
            } else {
                // 
            }
        }
    });
}

function fetchAddressFromGoogle(pincode) {
  jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: request_globals.request_url,
        data: {
            action: "skyfinityqc_get_address_by_postcode",
            pincode: pincode
        },
        success: function(response) {
            if (response.success == true) {
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                if (response.address) {
                    jQuery('#city').val(response.address.city);
                    jQuery('#state').val(response.address.state);
                    jQuery('#country').val(response.address.country);
                }
            } else {
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                jQuery('#city').val('');
                jQuery('#state').val('');
                jQuery('#country').val('');
            }
        }
    });
}

function checkAddressTypeValue() {
    let val = jQuery('input[name="address_type_select"]:checked').val();
    if (val == 'Other') {
        jQuery('#address_type_other').show();
    } else {
        jQuery('#address_type_other').hide();

    }
}

jQuery(document).ready(function() {
    jQuery(document).on("change", '#pincode', async function(e) {
        const value = e.target.value;
        if (value?.length >= 4) {
            fetchAddressFromGoogle(value);
        }
    });
    checkAddressTypeValue();
    jQuery(document).on("change", '#address_type_other', function(e) {
        jQuery('#address_type').val(e.target.value);
    });
    jQuery(document).on("change", 'input[name="address_type_select"]', function(e) {
        let val = '';
        if (jQuery(this).is(':checked')) {
            val = e.target.value;
            if (val != 'Other') {
                jQuery('#address_type').val(val);
                jQuery('#address_type_error').text('');
            }
        }
        checkAddressTypeValue();
    });
    jQuery(document).on('click', '.address-action-icon', function() {
        var actionMenu = jQuery(this).closest('.address-actions').find('.address-action-menu');
        jQuery(actionMenu).toggleClass('show');
    });
    jQuery(document).mouseup(function(e) {
        var container = jQuery(".address-action-menu");
        var buttons = jQuery(".address-action-icon");

        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0 && !buttons.is(e.target) && buttons.has(e.target).length === 0) {
            container.removeClass('show');
        }
    });
});