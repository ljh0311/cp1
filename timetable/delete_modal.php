<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Class</h5>
                <button type="button" id="deleteClose" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <form action="../classes/process_deleteClass.php" method="POST">
            <p>Are you sure you want to remove the following class?</p>
                <input type="hidden" id="deleteClassId" name="deleteClassId">
                <div class="form-group">
                    <label for="className">Class Name:</label>
                    <input type="text" class="form-control" id="deleteClassName" disabled>
                </div>
                <div class="form-group">
                    <label for="selectDate">Date</label>
                    <input type="date" class="form-control" id="deleteSelectDate" name="selectDate" disabled>
                </div>
                <div class="form-group">
                    <label for="startTime">Start Time:</label>
                    <input type="text" class="form-control" id="deleteStartTime" disabled>
                </div>
                <div class="form-group">
                    <label for="endTime">End Time:</label>
                    <input type="text" class="form-control" id="deleteEndTime" disabled>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
</form>
        </div>
    </div>
</div>
