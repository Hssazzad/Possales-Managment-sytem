<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('mainmenu.update', $item) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit &mdash; {{ $item->text }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Menu Name <span class="text-danger">*</span></label>
                        <input type="text" name="text" class="form-control" value="{{ $item->text }}" required>
                    </div>
                    <div class="form-group">
                        <label>URL <span class="text-danger">*</span></label>
                        <input type="text" name="url" class="form-control" value="{{ $item->url }}" required>
                    </div>
                    <div class="form-group">
                        <label>Icon Class <span class="text-danger">*</span></label>
                        <input type="text" name="icon" class="form-control" value="{{ $item->icon }}" required>
                    </div>
                    <div class="form-group">
                        <label>Controller Name</label>
                        <input type="text" name="controller_name" class="form-control" value="{{ $item->controller_name }}" placeholder="e.g. AccountingController">
                    </div>
                    <div class="form-group">
                        <label>Main Route Name</label>
                        <input type="text" name="main_route" class="form-control" value="{{ $item->main_route }}" placeholder="e.g. accounting">
                    </div>
                    <div class="form-group">
                        <label>Display Order <span class="text-danger">*</span></label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $item->sort_order }}" min="1" required>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_active" id="active{{ $item->id }}" value="1"
                                   class="custom-control-input" {{ $item->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="active{{ $item->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
