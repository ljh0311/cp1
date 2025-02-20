let classId = -1;  // Indicates no classId
let className = "";
let currentWeekStartDate = new Date();
const daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];


document.addEventListener("DOMContentLoaded", function () {
  $('#createClassForm')[0].reset();

  if (userTypeID === 2) {
    $('#studentEditClassForm')[0].reset();
  } else if (userTypeID === 3) {
    $('#tutorEditClassForm')[0].reset();
  }
 
  initializeTimetable();
  setupEventListeners();

  
});

$(document).ready(function() {
  $('#createButton').click(function() {
    $('#createModal').modal('show');
  });

  $('#editClose').click(function() {
    $('#editModal').modal('hide');
  });

  $('#createClose').click(function() {
    $('#createModal').modal('hide');
  });
  
  $('#deleteClose').click(function() {
    $('#deleteModal').modal('hide');
  });

  $('#editModal').on('hidden.bs.modal', function () {
      if (userTypeID === 2) {
        $('#studentEditClassForm')[0].reset();
      } else if (userTypeID === 3) {
        $('#tutorEditClassForm')[0].reset();
      }
      
  });

  $('#createModal').on('hidden.bs.modal', function () {
      $('#createClassForm')[0].reset();
  });
});

function initializeTimetable() {
  if (userTypeID === 3) {
    getTutorTimetable(userID);
  } else if (userTypeID === 2) {
    getStudentEnrollments(userID);
  }

  document.getElementById('createUserId').value = userID;
}

function setupEventListeners() {
  document.getElementById('editButton').addEventListener('click', () => {
    $('#editModal').modal('show');
    getClassDetails("update");
  });

  if (userTypeID === 2) {
    document.getElementById('prevWeekButton').addEventListener('click', () => adjustCurrentWeek(-7));
    document.getElementById('nextWeekButton').addEventListener('click', () => adjustCurrentWeek(7));
  } else if (userTypeID === 3) {
    document.getElementById('deleteButton').addEventListener('click', () => {
      $('#deleteModal').modal('show');
      getClassDetails("delete");
    });
  }

}

/* AJAX CALLS */
function getTutorTimetable(userId) {
  fetch('../timetable/get_tutorTimetable.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: userId })
  })
    .then(response => response.json())
    .then(data => populateListTimetable(data, daysOfWeek))
    .catch(error => console.error('Error fetching data:', error));
}

function getStudentEnrollments(userId) {
  fetch('../timetable/get_timetable.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: userId })
  })
    .then(response => response.json())
    .then(data => populateTimetable(data, currentWeekStartDate, userTypeID))
    .catch(error => console.error('Error fetching data:', error));
}

function adjustCurrentWeek(days) {
  currentWeekStartDate.setDate(currentWeekStartDate.getDate() + days);
  initializeTimetable();
}

// Class management functions
function getClassDetails(type) {
  fetch('../classes/get_class.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ classId })
  })
    .then(response => response.json())
    .then(data => {
      fillModalFields(type, data);
      if (type === "update" && userTypeID === 2) {
        className = data.className;
        getAllSpecificClasses(className);
      }
    })
    .catch(error => console.error('Error fetching data:', error));
}

function updateClassDetails() {
  const updatedDetails = {
    classId: document.getElementById('tutorUpdateClassId').value,
    className: document.getElementById('tutorUpdateClassName').value,
    date: document.getElementById('tutorUpdateSelectDate').value,
    startTime: document.getElementById('tutorUpdateStartTime').value,
    endTime: document.getElementById('tutorUpdateEndTime').value,
    capacity: document.getElementById('tutorUpdateCapacity').value,
    price: document.getElementById('tutorUpdatePrice').value
  };

  fetch('../classes/process_updateClass.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(updatedDetails)
  })
    .then(response => response.json())
    .then(data => showAlert(data.message, data.status === 'success' ? 'success' : 'danger'))
    .catch(error => showAlert('Error updating class, please try again.', 'danger'));
}

function deleteClass() {
  fetch('../classes/delete_class.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ classId })
  })
    .then(response => response.json())
    .then(data => showAlert('Class deleted successfully', 'success'))
    .catch(error => showAlert('Error deleting class, please try again.', 'danger'));
}

function fillModalFields(type, data) {
  if (type === "delete") {
    document.getElementById('deleteClassId').value = data.classId;
    document.getElementById('deleteClassName').value = data.className;
    document.getElementById('deleteSelectDate').value = data.date;
    document.getElementById('deleteStartTime').value = data.startTime;
    document.getElementById('deleteEndTime').value = data.endTime;
  } else if (type === "update") {
    if (userTypeID === 3) {
      document.getElementById("tutorUpdateClassId").value = data.classId;
      document.getElementById("tutorUpdateClassName").value = data.className;
      document.getElementById("tutorUpdateSelectDate").value = data.date;
      document.getElementById("tutorUpdateStartTime").value = data.startTime;
      document.getElementById("tutorUpdateEndTime").value = data.endTime;
      document.getElementById("tutorUpdateCapacity").value = data.capacity;
      document.getElementById("tutorUpdatePrice").value = data.price;
    } else if (userTypeID === 2) {
      document.getElementById("studentUpdateClassId").value = data.classId;
      document.getElementById("studentUpdateClassName").value = data.className;
      getAllSpecificClasses(data.className);
    }
  }
}

function getAllSpecificClasses(className) {
  fetch('../classes/get_specificClasses.php', {
    method: 'POST',
    body: JSON.stringify({ className: className, }),
    headers: { 'Content-Type': 'application/json' }
  })
    .then(response => { return response.json(); })
    .then(data => { populateSelect(data) })
    .catch(error => console.error('Error fetching data:', error));
}

function populateSelect(data) {
  const selectElement = document.getElementById('studentSelectDate');
  selectElement.innerHTML = ''; 

  // Loop through the data and create options
  data.forEach(item => {
    const option = document.createElement('option');
    const formattedOption = `${item.date} (${item.startTime} - ${item.endTime})`;
    option.value = item.classId; 
    option.textContent = formattedOption; 
    selectElement.appendChild(option); 
  });
}
