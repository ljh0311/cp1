<!-- Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createModalLabel">Create New Class</h5>
        <button type="button" id="createClose" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
        <div class="modal-body">
        <form id="createClassForm" action="../classes/process_createClass.php" method="POST">
        <input type="hidden" id="createUserId" name="createUserId">
          <div class="form-group">
            <label for="class-name">Class Name</label>
            <input type="text" class="form-control" id="class-name" name="class-name" required>
          </div>
          <div class="form-group">
            <label for="class-date">Date</label>
            <input type="date" class="form-control" id="class-date" name="class-date" required>
          </div>
          <div class="form-group">
            <label for="start-time">Start Time</label>
            <input type="time" class="form-control" id="start-time" name="start-time" required>
          </div>
          <div class="form-group">
            <label for="end-time">End Time</label>
            <input type="time" class="form-control" id="end-time" name="end-time" required>
          </div>
          <div class="form-group">
            <label for="class-price">Price</label>
            <input type="number" step="0.01" class="form-control" id="class-price" name="class-price" required>
          </div>
          <div class="form-group">
            <label for="class-capacity">Capacity</label>
            <input type="number" class="form-control" id="class-capacity" name="class-capacity" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Create Class</button>
        </div>
      </form>
    </div>
  </div>
</div>
