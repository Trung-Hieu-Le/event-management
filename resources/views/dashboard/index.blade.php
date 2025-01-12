@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                <div class="card-title">My Calendar</div>
                <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#eventAdd">Add Event</a>
            </div>

            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    <!-- Modal Thêm Sự Kiện -->
    <div class="modal fade" id="eventAdd" tabindex="-1" role="dialog" aria-labelledby="eventAddLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventAddLabel">Add Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('events.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="author_id" value="{{ auth()->user()->id }}">
                        <div class="form-group mb-3">
                            <label for="newEventTitle">Title</label>
                            <input type="text" name="title" id="newEventTitle" class="form-control" placeholder="Event Title" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="newStartTime">Start Time</label>
                            <input type="datetime-local" name="start_time" id="newStartTime" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="newEndTime">End Time</label>
                            <input type="datetime-local" name="end_time" id="newEndTime" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="newType">Type</label>
                            <select name="type" id="newType" class="form-control">
                                <option value="personal">Personal</option>
                                <option value="community">Community</option>
                            </select>
                        <div class="form-group mb-3">
                            <label for="newLocation">Location</label>
                            <input type="text" name="location" id="newLocation" class="form-control" placeholder="Location">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Event</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal sửa sự kiện -->
    <div class="modal" id="editEventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event</h5>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" id="eventId">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title_edit" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="datetime-local" id="start_time" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="end_time">End Time</label>
                            <input type="datetime-local" id="end_time" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type_edit" class="form-control">
                                <option value="personal">Personal</option>
                                <option value="community">Community</option>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-update-event">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>    
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        timeZone: 'local',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: true,
        droppable: true,
        dayMaxEvents: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        },
        events: @json($events),

        eventClick: function(info) {
            var event = info.event;
            console.log(event);
            
            var eventIdField = document.getElementById('eventId');
            var titleEditField = document.getElementById('title_edit');
            var startTimeField = document.getElementById('start_time');
            var endTimeField = document.getElementById('end_time');
            var locationField = document.getElementById('location');
            var typeField = document.getElementById('type_edit');
        
            if (eventIdField) eventIdField.value = event.id || '';
            if (titleEditField) titleEditField.value = event.title || '';
            if (startTimeField) startTimeField.value = event.start ? convertToLocalTime(event.start) : '';
            if (endTimeField) endTimeField.value = event.end ? convertToLocalTime(event.end) : '';
            if (locationField) locationField.value = event.extendedProps.location || '';
            if (typeField) typeField.value = event.extendedProps.type || personal;
            $('#editEventModal').modal('show');
        },

        eventDrop: function (info) {
            var eventId = info.event.id;
            if (!eventId) {
                alert('Event ID is missing.');
                info.revert();
                return;
            }

            // Lấy ngày giờ mới sau khi kéo thả
            var updatedStart = info.event.start.toISOString();
            var updatedEnd = info.event.end ? info.event.end.toISOString() : null;

            // Gửi yêu cầu cập nhật tới server
            fetch(`/events/${eventId}/update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    start: updatedStart,
                    end: updatedEnd,
                }),
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Event updated successfully!');
                    } else {
                        alert(`Error: ${data.message}`);
                        info.revert(); // Khôi phục nếu lỗi
                    }
                })
                .catch(error => {
                    alert(`Error updating event: ${error.message}`);
                    info.revert(); // Khôi phục nếu lỗi
                });
        }

    });

    calendar.render();

    document.querySelector('.btn-update-event').addEventListener('click', function () {
        const eventId = document.getElementById('eventId').value;
        const title = document.getElementById('title_edit').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const location = document.getElementById('location').value;
        const type = document.getElementById('type_edit').value;

        if (!title || !startTime) {
            alert('Title and Start Time are required!');
            return;
        }

        fetch('/events/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                id: eventId,
                title: title,
                start_time: startTime,
                end_time: endTime,
                location: location,
                type: type,
            }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);

                    // Cập nhật sự kiện trong lịch
                    const event = calendar.getEventById(eventId);
                    if (event) {
                        event.setProp('title', title);
                        event.setStart(startTime);
                        event.setEnd(endTime);
                        event.setExtendedProp('location', location);
                    }

                    // Đóng modal
                    $('#editEventModal').modal('hide');
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                alert(`Error updating event: ${error.message}`);
            });
    });
});


// Hàm chuyển đổi định dạng datetime sang MySQL
function convertToLocalTime(date) {
    const local = new Date(date);
    const offset = local.getTimezoneOffset();
    return new Date(local.getTime() - offset * 60 * 1000).toISOString().slice(0, 16);
}
</script>
@endpush