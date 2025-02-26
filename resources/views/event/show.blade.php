@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $event->name }} <button class="btn btn-primary ms-3" onclick="showInviteModal()">Mời User</button></h2>
    <p>Thời gian: {{ $event->start_time }} - {{ $event->end_time }}</p>
    <p>Thành viên: 
        @foreach ($event->users as $user)
            @if ($user->id === $event->author_id)
                <strong>{{ $user->name }} (Author)</strong>,
            @else
                {{ $user->name }},
            @endif
        @endforeach
    </p>

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
                            'reworking' => 'warning'
                        ];
                        $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
                    @endphp
                    @foreach($statuses as $status => $color)
                        <th class="bg-{{ $color }} bg-gradient col-2" style="min-width: 120px;">
                            {{ ucfirst(str_replace('-', ' ', $status)) }}
                            @if($status !== 'deleted')
                                <button class="btn btn-sm btn-light text-dark ms-2 float-end" onclick="showTaskModal('{{ $status }}')">+</button>
                            @endif
                        </th>
                    @endforeach
                    <th class="bg-secondary bg-gradient col-2" style="min-width: 120px;">Deleted</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($statuses as $status => $color)
                        <td id="{{ $status }}" class="task-column pb-3" ondrop="drop(event)" ondragover="allowDrop(event)">
                            @foreach($event->tasks->where('status', $status)->where('deleted', 0) as $task)
                                @php
                                    $priority = strtolower($task->priority);
                                    $priorityColor = $priorityColors[$priority] ?? 'secondary';
                                @endphp
                                <div class="card task p-2 mb-2" draggable="true" 
                                    ondragstart="drag(event, {{ $task->id }})"
                                    onclick="editTask({{ json_encode($task) }})"
                                    style="min-width: 300px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    <div class="card-body">
                                        <span class="float-end text-{{ $priorityColor }}" title="Priority">
                                            <i class="fas fa-tag"></i>
                                        </span>
                                        <h6 class="card-title">{{ $task->title }}</h6>
                                        <!-- <p class="card-text text-muted mb-1">{{ $task->description }}</p> -->
                                        <small class="text-muted">
                                            🕒 {{ $task->start_time }} - {{ $task->end_time }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </td>
                    @endforeach
                    <td id="deleted" class="task-column pb-3">
                        @foreach($event->tasks->where('deleted', 1) as $task)
                            @php
                                $priority = strtolower($task->priority);
                                $priorityColor = $priorityColors[$priority] ?? 'secondary';
                            @endphp
                            <div class="card task p-2 mb-2"
                                onclick="confirmRestoreTask({{ $task->id }})"
                                style="min-width: 300px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                <div class="card-body">
                                    <span class="float-end text-{{ $priorityColor }}" title="Priority">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                    <h6 class="card-title">{{ $task->title }}</h6>
                                    <!-- <p class="card-text text-muted mb-1">{{ $task->description }}</p> -->
                                    <small class="text-muted">
                                        🕒 {{ $task->start_time }} - {{ $task->end_time }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="taskModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title modal-title-task">Thêm Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        <input type="hidden" id="task_id">
                        <input type="hidden" id="event_id" value="{{ $event->id }}">

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
                                <option value="low">Thấp</option>
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
                            <select id="assigned_to" class="form-select" multiple>
                                @foreach ($event->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select id="status" class="form-select" disabled>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ ucfirst($key) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="taskModalFooter">
                            <button type="button" class="btn btn-primary" id="taskSubmitBtn">Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mời người dùng vào sự kiện</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="inviteInput">Nhập email hoặc tên:</label>
                    <input type="text" id="inviteInput" class="form-control" placeholder="Nhập email hoặc tên" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-primary" onclick="sendInvite()">Gửi lời mời</button>
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
        let inviterId = "{{ auth()->user()->id }}"; // Lấy id của người mời
        console.log(identifier, eventId, inviterId);
        
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
            let inviteModal = new bootstrap.Modal(document.getElementById("inviteModal"));
            inviteModal.hide();
        });
    }

    function getSelectedValues(selectElement) {
        return Array.from(selectElement.selectedOptions).map(option => option.value);
    }

    function showTaskModal(status) {
        document.getElementById("taskForm").reset(); // Reset form để tránh dữ liệu cũ
        document.getElementById("task_id").value = ""; // Xóa task_id để đảm bảo là thêm mới
        document.getElementById("status").value = status;
        document.querySelector(".modal-title-task").textContent = "Thêm Task"; // Đổi tiêu đề modal
        document.getElementById("taskSubmitBtn").setAttribute("onclick", "createTask()"); // Đổi nút thành tạo mới
        let deleteBtn = document.getElementById("deleteTaskBtn");
        if (deleteBtn) deleteBtn.remove(); // Xóa nút xóa nếu có

        let modal = new bootstrap.Modal(document.getElementById("taskModal"));
        modal.show();
    }

    function editTask(task) {
        document.getElementById("task_id").value = task.id;
        document.getElementById("title").value = task.title;
        document.getElementById("description").value = task.description || "";
        document.getElementById("priority").value = task.priority.toLowerCase();
        document.getElementById("start_time").value = task.start_time;
        document.getElementById("end_time").value = task.end_time;
        document.getElementById("status").value = task.status; 
        let assignedSelect = document.getElementById("assigned_to");
        let assignedUsers = Array.isArray(task.assigned_to) ? task.assigned_to : [task.assigned_to];
        for (let option of assignedSelect.options) {
            option.selected = assignedUsers.includes(option.value);
        }

        document.querySelector(".modal-title-task").textContent = "Chỉnh sửa Task"; // Đổi tiêu đề modal
        document.getElementById("taskSubmitBtn").setAttribute("onclick", "updateTask()"); // Đổi nút thành cập nhật

        let deleteBtn = document.getElementById("deleteTaskBtn");
        if (!deleteBtn) {
            deleteBtn = document.createElement("button");
            deleteBtn.id = "deleteTaskBtn";
            deleteBtn.className = "btn btn-danger ms-2";
            deleteBtn.textContent = "Xóa Task";
            deleteBtn.onclick = function () {
                confirmDeleteTask(task.id);
            };
            document.getElementById("taskModalFooter").appendChild(deleteBtn);
        } else {
            deleteBtn.onclick = function () {
                confirmDeleteTask(task.id);
            };
        }
        
        let modal = new bootstrap.Modal(document.getElementById("taskModal"));
        modal.show();
    }

    function createTask() {
        let data = {
            title: document.getElementById('title').value,
            description: document.getElementById('description').value,
            status: document.getElementById('status').value,
            event_id: document.getElementById('event_id').value,
            assigned_to: getSelectedValues(document.getElementById('assigned_to')),
            start_time: document.getElementById('start_time').value,
            end_time: document.getElementById('end_time').value,
            priority: document.getElementById('priority').value,
            _token: "{{ csrf_token() }}"
        };
        console.log(data);
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
    function updateTask() {
        let taskId = document.getElementById("task_id").value;
        let data = {
            title: document.getElementById("title").value,
            description: document.getElementById("description").value,
            priority: document.getElementById("priority").value,
            start_time: document.getElementById("start_time").value,
            end_time: document.getElementById("end_time").value,
            assigned_to: getSelectedValues(document.getElementById("assigned_to")),
            _token: "{{ csrf_token() }}"
        };

        fetch(`/task/${taskId}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            alert(result.message || "Task đã được cập nhật!");
            location.reload();
        });
    }

    function confirmDeleteTask(taskId) {
        if (confirm("Bạn có chắc chắn muốn xóa task này không?")) {
            fetch(`/task/${taskId}/delete`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    deleted: 1,
                    _token: "{{ csrf_token() }}"
                })
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message || "Task đã bị xóa!");
                location.reload();
            });
        }
    }

    function confirmRestoreTask(taskId) {        
        if (confirm("Bạn có chắc chắn muốn khôi phục task này không?")) {
            fetch(`/task/${taskId}/restore`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    deleted: 0,
                    _token: "{{ csrf_token() }}"
                })
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message || "Task đã được khôi phục!");
                location.reload();
            });
        }
    }
</script>
@endsection
