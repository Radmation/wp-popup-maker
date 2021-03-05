import validate from "validate.js";

/**
 * KbiAdmin Js. Contains the code for the CMS admin functionality.
 */
class KbiAdmin {

  /**
   * The constructor.
   */
  constructor() {
    this.setEventListeners();
  }

  /**
   * Set all event listeners for website.
   */
  setEventListeners() {
    const addFieldMappingButton = document.querySelectorAll('.add-field-mapping');

    if (addFieldMappingButton) {
      for (let i = 0; i < addFieldMappingButton.length; i++) {
        addFieldMappingButton[i].addEventListener('click', function (e) {
          e.preventDefault();

          // Field Mappings Container
          const fieldMappingsContainer = addFieldMappingButton[i].parentElement.parentElement.getElementsByClassName("field-mappings")[0];

          // Check if any field mapping inputs do not have text in them.
          let fieldMappingInput = fieldMappingsContainer.getElementsByClassName("mapping-field");
          let count = (typeof fieldMappingInput === "undefined" || fieldMappingInput.length === 0) ? 0 : fieldMappingInput.length;

          if (count > 0) {
            let empty = false;
            for (let i = 0; i < fieldMappingInput.length; i++) {
              // If any of the inputs do not have text in them show a message.
              if (fieldMappingInput[i].value === "") {
                empty = true;
              }
            }

            if (empty) {
              this.createMessageAlert(fieldMappingsContainer);
            } else {
              this.addFieldMappingInputs(fieldMappingsContainer);
            }
          } else {
            this.addFieldMappingInputs(fieldMappingsContainer);
          }
        }.bind(this), false);
      }
    }

    this.bindFieldMappings();
  }

  /**
   * Add field mapping row.
   */
  addFieldMappingInputs(fieldMappingsContainer) {

    // Remove messages first.
    this.removeMessageAlert(fieldMappingsContainer);

    let fieldMappingRow = fieldMappingsContainer.getElementsByClassName("field-mapping-row");
    let count = (typeof fieldMappingRow === "undefined" || fieldMappingRow.length === 0) ? 1 : fieldMappingRow.length + 1;

    let formRow = document.createElement("div");
    formRow.className = "row align-items-center field-mapping-row";

    let columnFirstGroup = document.createElement("div");
    columnFirstGroup.className = "col form-group";

    let formFieldFirst = document.createElement("input");
    formFieldFirst.type = "text";
    formFieldFirst.name = "map-from-" + count;
    formFieldFirst.className = "mapping-field mapping-field-from form-control";

    let columnSecondGroup = document.createElement("div");
    columnSecondGroup.className = "col-md-auto form-group";
    columnSecondGroup.innerHTML = "<code>=></code>";

    let columnThirdGroup = document.createElement("div");
    columnThirdGroup.className = "col form-group";

    let formFieldSecond = document.createElement("input");
    formFieldSecond.type = "text";
    formFieldSecond.name = "map-to-" + count;
    formFieldSecond.className = "mapping-field mapping-field-to form-control";

    let columnForthGroup = document.createElement("div");
    columnForthGroup.className = "col-md-auto form-group";
    columnForthGroup.innerHTML = "<button class='btn btn-danger remove-field-mapping'>Remove</button>";

    columnFirstGroup.appendChild(formFieldFirst);
    columnThirdGroup.appendChild(formFieldSecond);

    formRow.appendChild(columnFirstGroup);
    formRow.appendChild(columnSecondGroup);
    formRow.appendChild(columnThirdGroup);
    formRow.appendChild(columnForthGroup);

    fieldMappingsContainer.appendChild(formRow);

    this.bindFieldMappings();
  }

  /**
   * Bind remove button event.
   */
  bindFieldMappings() {
    let removeFieldMappingButtons = document.querySelectorAll(".remove-field-mapping");

    for (let i = 0; i < removeFieldMappingButtons.length; i++) {
      removeFieldMappingButtons[i].addEventListener('click', function (e) {
        e.preventDefault();
        const mappingRow = removeFieldMappingButtons[i].parentElement.parentElement;
        const fieldMappingsContainer = removeFieldMappingButtons[i].parentElement.parentElement.parentElement;

        mappingRow.remove();
        this.renameFieldMappings(fieldMappingsContainer);
      }.bind(this), false);
    }
  }

  /**
   * Rename field mappings.
   */
  renameFieldMappings(fieldMappingsContainer) {
    let mappingFieldsFrom = fieldMappingsContainer.getElementsByClassName("mapping-field-from");
    let mappingFieldsTo = fieldMappingsContainer.getElementsByClassName("mapping-field-to");

    if (mappingFieldsFrom !== null) {
      for (let i = 0; i < mappingFieldsFrom.length; i++) {
        mappingFieldsFrom[i].name = "map-from-" + (i + 1).toString();
      }
    }

    if (mappingFieldsTo !== null) {
      for (let i = 0; i < mappingFieldsTo.length; i++) {
        mappingFieldsTo[i].name = "map-to-" + (i + 1).toString();
      }
    }
  }

  /**
   * Remove message.
   */
  removeMessageAlert(fieldMappingsContainer) {
    const mappingAlerts = fieldMappingsContainer.getElementsByClassName("mappingAlert");

    if (typeof mappingAlerts !== "undefined" && mappingAlerts.length > 0) {
      for (let i = 0; i < mappingAlerts.length; i++) {
        mappingAlerts[i].remove();
      }
    }
  }

  /**
   * Create message.
   */
  createMessageAlert(fieldMappingsContainer) {
    const mappingAlerts = fieldMappingsContainer.getElementsByClassName("mappingAlert");

    if (mappingAlerts.length === 0) {
      let message = document.createElement("div");
      message.innerHTML = "These values are required before adding a new button.";
      message.className = "alert alert-warning mappingAlert";

      fieldMappingsContainer.appendChild(message);
    }
  }
}

const kbiAdmin = new KbiAdmin();