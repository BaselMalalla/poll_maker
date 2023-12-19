document.addEventListener("DOMContentLoaded", () => {
  const options = document.getElementById("options");
  const addOptionButton = document.getElementById("addOption");
  const removeOptionButton = document.getElementById("removeOption");
  const timedOptions = document.querySelector(".timed-options");

  function appendValidationMessage(message) {
    const validationMessages = document.getElementById("validation-messages");

    if (validationMessages) {
      const newParagraph = document.createElement("p");
      newParagraph.textContent = message;
      validationMessages.appendChild(newParagraph);
    } else {
      console.error("Validation messages element not found!");
    }
  }

  addOptionButton.addEventListener("click", () => {
    const inputTags = options.getElementsByTagName("input");
    const newField = document.createElement("input");

    const newOptionNumber = inputTags.length + 1; // Calculate the new option number
    Object.assign(newField, {
      type: "text",
      name: "options[]",
      classList: ["form-input input-options"],
      placeholder: `Option ${newOptionNumber}`,
    });

    options.appendChild(newField);
  });

  removeOptionButton.addEventListener("click", () => {
    const inputTags = options.getElementsByTagName("input");
    if (inputTags.length > 2) {
      options.removeChild(inputTags[inputTags.length - 1]);
    }
  });

  // Event listener for radio buttons
  document.querySelectorAll('input[name="poll_duration"]').forEach((radio) => {
    radio.addEventListener("change", (event) => {
      timedOptions.hidden = event.target.value !== "timed";
    });
  });
});
