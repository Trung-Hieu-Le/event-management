@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $event->name }}</h2>
    <p>Thời gian: {{ $event->start_time }} - {{ $event->end_time }}</p>
    <p>Mô tả: {{ $event->description }}</p>

    <button class="btn btn-primary" onclick="showInviteModal()">Mời User</button>

    <div id="inviteModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="inviteModal" aria-hidden="true">
        <div class="modal-dialog">
            <form id="inviteForm" method="POST" action="{{ route('event.invite') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="method" value="POST">
                <input type="hidden" name="event_id" id="event_id" value="{{ $id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Gửi lời mời</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="inviteInput" class="form-control" placeholder="Nhập username hoặc email">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" onclick="sendInvite()">Gửi lời mời</button>
                        <button class="btn btn-secondary" onclick="closeInviteModal()">Đóng</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive mt-4" style="max-width: 100%; overflow-x: auto;">
        <table class="table table-bordered" style="min-width: 1200px;">
            <thead>
                <tr>
                    @php
                        $statuses = [
                            'to-do' => 'info', 
                            'doing' => 'primary', 
                            'done' => 'success', 
                            'failed' => 'danger', 
                            'reworking' => 'warning', 
                            'deleted' => 'secondary'
                        ];
                    @endphp
                    @foreach($statuses as $status => $color)
                        <th class="bg-{{ $color }} bg-gradient col-2">
                            {{ ucfirst(str_replace('-', ' ', $status)) }}
                            @if($status !== 'deleted')
                                <button class="btn btn-sm btn-light text-dark ms-2" onclick="showTaskModal('{{ $status }}')">+</button>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($statuses as $status => $color)
                        <td id="{{ $status }}" class="task-column" ondrop="drop(event)" ondragover="allowDrop(event)">
                            @foreach($event->tasks->where('status', $status)->where('deleted', 0) as $task)
                                <div class="task" draggable="true" ondragstart="drag(event, {{ $task->id }})">
                                    {{ $task->title }}
                                </div>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <div id="taskModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tạo Task Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        <input type="hidden" id="event_id" value="{{ $event->id }}">
                        <input type="hidden" id="status">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề</label>
                            <input type="text" id="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea id="description" class="form-control"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Mức độ ưu tiên</label>
                            <select id="priority" class="form-select">
                                <option value="low" selected>Thấp</option>
                                <option value="medium">Trung bình</option>
                                <option value="high">Cao</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="start_time" class="form-label">Bắt đầu</label>
                            <input type="datetime-local" id="start_time" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="end_time" class="form-label">Kết thúc</label>
                            <input type="datetime-local" id="end_time" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Giao cho</label>
                            <input type="text" id="assigned_to" class="form-control" placeholder="User ID hoặc Email">
                        </div>

                        <button type="button" class="btn btn-success" onclick="createTask()">Tạo Task</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function allowDrop(event) {
        event.preventDefault();
    }

    function drag(event, taskId) {
        event.dataTransfer.setData("task_id", taskId);
    }

    function drop(event) {
        event.preventDefault();
        let taskId = event.dataTransfer.getData("task_id");
        let newStatus = event.target.id;

        fetch("{{ route('task.updateStatus') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ task_id: taskId, status: newStatus })
        }).then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
    }
    function showInviteModal() {
        var modal = new bootstrap.Modal(document.getElementById('inviteModal'));
        modal.show();
    }

    function sendInvite() {
        let identifier = document.getElementById('inviteInput').value;
        let eventId = "{{ $event->id }}";

        fetch("{{ route('event.invite') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ identifier, event_id: eventId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success || data.error);
            closeInviteModal();
        });
    }

    function showTaskModal(status) {
        document.getElementById('status').value = status;
        let modal = new bootstrap.Modal(document.getElementById('taskModal'));
        modal.show();
    }

    function createTask() {
        let data = {
            title: document.getElementById('title').value,
            description: document.getElementById('description').value,
            status: document.getElementById('status').value,
            event_id: document.getElementById('event_id').value,
            assigned_to: document.getElementById('assigned_to').value,
            start_time: document.getElementById('start_time').value,
            end_time: document.getElementById('end_time').value,
            priority: document.getElementById('priority').value,
            _token: "{{ csrf_token() }}"
        };

        fetch("{{ route('task.store') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            alert(result.message || "Task đã được tạo thành công!");
            location.reload();
        });
    }

</script>
@endsection
