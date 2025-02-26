@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Danh sách dự án của bạn</h2>

        <form action="{{ route('home') }}" method="GET" class="d-flex">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm dự án..." class="form-control me-2">
            <select name="sort" class="form-select me-2" onchange="this.form.submit()">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Sắp xếp theo mới nhất</option>
                <option value="most_tasks" {{ request('sort') == 'most_tasks' ? 'selected' : '' }}>Sắp xếp theo số task nhiều nhất</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Sắp xếp theo cũ nhất</option>
            </select>
            <select name="filter" class="form-select me-2" onchange="this.form.submit()">
                <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>Tất cả event</option>
                <option value="author" {{ request('filter') == 'author' ? 'selected' : '' }}>Event do bạn làm Author</option>
                <option value="joined" {{ request('filter') == 'joined' ? 'selected' : '' }}>Event bạn được join vào</option>
            </select>
        </form>

        <button class="btn btn-success" onclick="showCreateModal()">➕ Thêm Dự Án</button>
    </div>

    @if ($events->isEmpty())
        <p>Bạn không tham gia dự án nào.</p>
    @else
        <div class="row">
            @foreach ($events as $event)
                <div class="col-md-3">
                    <div class="card mb-3">
                        @if($event->image)
                            <a href="{{ route('event.show', $event->id) }}">
                                <img src="{{ asset('storage/' . $event->image) }}" class="card-img-top" alt="Cover">
                            </a>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('event.show', $event->id) }}">{{ $event->name }}</a>
                            </h5>
                            <p>📅 {{ $event->start_time }} - {{ $event->end_time }}</p>
                            
                            @if (Auth::id() === $event->author_id)
                                <button class="btn btn-primary btn-sm" onclick="editEvent({{ $event->id }}, '{{ $event->name }}', '{{ $event->start_time }}', '{{ $event->end_time }}')">✏️ Sửa</button>
                                <form action="{{ route('events.destroy', $event) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa dự án này không?')">🗑 Xóa</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<!-- Modal thêm/sửa event -->
<div id="eventModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form id="eventForm" method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <input type="hidden" name="event_id" id="event_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm Dự Án</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="name">Tên dự án</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Tên dự án" required>
                    
                    <label for="start_time" class="mt-2">Ngày bắt đầu</label>
                    <input type="datetime-local" id="start_time" name="start_time" class="form-control">
                    
                    <label for="end_time" class="mt-2">Ngày kết thúc</label>
                    <input type="datetime-local" id="end_time" name="end_time" class="form-control">

                    <label for="image" class="mt-2">Hình ảnh</label>
                    <input type="file" id="image" name="image" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('modalTitle').innerText = 'Thêm Dự Án';
    document.getElementById('eventForm').action = "{{ route('events.store') }}";
    document.getElementById('method').value = 'POST';
    var modal = new bootstrap.Modal(document.getElementById('eventModal'));
    modal.show();
}

function editEvent(id, name, start_time, end_time) {
    document.getElementById('modalTitle').innerText = 'Sửa Dự Án';
    document.getElementById('eventForm').action = "/update-events/" + id;
    document.getElementById('method').value = 'PUT';
    document.getElementById('name').value = name;
    document.getElementById('start_time').value = start_time;
    document.getElementById('end_time').value = end_time;
    document.getElementById('eventModal').style.display = 'block';
    var modal = new bootstrap.Modal(document.getElementById('eventModal'));
    modal.show();
}
</script>
@endsection
