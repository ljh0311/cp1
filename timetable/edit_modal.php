<!-- Inside the body tag -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <?php echo $_SESSION['user_id'] == 2 ?  'Reschedule Class' : 'Edit Class Details' ; ?>
                </h5>
                <button type="button" id="editClose" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                /*$_SESSION['user_id']*/
                if ($_SESSION['userTypeId'] == 2) {
                    echo '<form id="studentEditClassForm" action="../classes/process_updateStudentClass.php" method="POST">
                    <input type="hidden" id="studentUpdateClassId" name="studentUpdateClassId">
                    <div class="form-group">
                        <label for="studentClassName">Class Name (Disabled)</label>
                        <input type="text" class="form-control" id="studentUpdateClassName" name="studentUpdateClassName" disabled>
                    </div>
                    <div class="form-group">
                        <label for="studentSelectDate">Select Date</label>
                        <select class="form-control" id="studentSelectDate" name="studentSelectDate">
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="updateStudentButton">Update</button>
                    </div>
                </form>';
                } else if ($_SESSION['userTypeId'] == 3) {
                    echo '<form id="tutorEditClassForm" action="../classes/process_updateClass.php" method="POST">
                    <input type="hidden" id="tutorUpdateClassId" name="classId">
                    <div class="form-group">
                        <label for="className">Class Name</label>
                        <input type="text" class="form-control" id="tutorUpdateClassName" name="className">
                    </div>
                    <div class="form-group">
                        <label for="selectDate">Select Date</label>
                        <input type="date" class="form-control" id="tutorUpdateSelectDate" name="selectDate">
                    </div>
                    <div class="form-group">
                        <label for="startTime">Start Time</label>
                        <input type="time" class="form-control" id="tutorUpdateStartTime" name="startTime">
                    </div>
                    <div class="form-group">
                        <label for="endTime">End Time</label>
                        <input type="time" class="form-control" id="tutorUpdateEndTime" name="endTime">
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" class="form-control" id="tutorUpdateCapacity" name="capacity">
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" class="form-control" id="tutorUpdatePrice" name="price" step="0.01">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="updateButton">Update</button>
                    </div>
                </form>';
                }
                ?>
            </div>
        </div>
    </div>
</div>