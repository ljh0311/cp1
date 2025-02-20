document.addEventListener("DOMContentLoaded", function () {
  // Assuming your form and inputs already have these IDs
  document.getElementById("selectedClassesInput").value =
    JSON.stringify(selectedClasses);
  document.getElementById("totalAmountInput").value = totalAmount.toFixed(2);

  // Any additional logic related to selectedClasses and totalAmount can go here
});
