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
    <div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="eventEditLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event</h5>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        @csrf
                        <input type="hidden" id="eventId">
                        <div class="form-group">
                            <label for="title_edit">Title</label>
                            <input type="text" id="title_edit" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="start_time_edit">Start Time</label>
                            <input type="datetime-local" id="start_time_edit" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="end_time_edit">End Time</label>
                            <input type="datetime-local" id="end_time_edit" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="type_edit">Type</label>
                            <select id="type_edit" class="form-control">
                                <option value="personal">Personal</option>
                                <option value="community">Community</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location_edit">Location</label>
                            <input type="text" id="location_edit" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-update-event">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> 

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
                            <input type="text" name="title" id="title_add" class="form-control" placeholder="Event Title" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="newStartTime">Start Time</label>
                            <input type="datetime-local" name="start_time" id="start_time_add" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="newEndTime">End Time</label>
                            <input type="datetime-local" name="end_time" id="end_time_add" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="newType">Type</label>
                            <select name="type" id="type_add" class="form-control">
                                <option value="personal">Personal</option>
                                <option value="community">Community</option>
                            </select>
                        <div class="form-group mb-3">
                            <label for="newLocation">Location</label>
                            <input type="text" name="location" id="location_add" class="form-control" placeholder="Location">
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
            var eventIdField = document.getElementById('eventId');
            var titleEditField = document.getElementById('title_edit');
            var startTimeField = document.getElementById('start_time_edit');
            var endTimeField = document.getElementById('end_time_edit');
            var locationField = document.getElementById('location_edit');
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
        const startTime = document.getElementById('start_time_edit').value;
        const endTime = document.getElementById('end_time_edit').value;
        const location = document.getElementById('location_edit').value;
        const type = document.getElementById('type_edit').value;
        // console.log(eventId, title, startTime, endTime, location, type);
        
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
            .then(response => {
                console.log('Response:', response);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Received non-JSON response");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    $('#editEventModal').modal('hide');
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error(error);
                alert(`Error updating event: ${error.message}`);
            });
    });
});


function convertToLocalTime(date) {
    const local = new Date(date);
    const offset = local.getTimezoneOffset();
    return new Date(local.getTime() - offset * 60 * 1000).toISOString().slice(0, 16);
}
</script>
@endpush