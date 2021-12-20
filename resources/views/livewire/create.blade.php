<div class="row">
    <div class="col-md-8 offset-md-2">
        <form wire:submit.prevent="store" enctype="multipart/form-data">
            <h5 class="modal-title" id="staticBackdropLabel">Add Product</h5>
            <div class="row">
                <div class="col-8">
                    <label for="validationCustom01" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" placeHolder="Enter Product Title"
                        wire:model.defer="title" />
                    @error('title')<span class="text-danger error">{{ $message }}</span>@enderror
                </div>
                <div class="col-4">
                    <label for="validationCustom02" class="form-label">Price</label>
                    <input type="text" class="form-control" id="price" placeHolder="Enter Product Price"
                        wire:model.defer="price" />
                    @error('price')
                        <span class="text-danger error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-12 my-2">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" rows="3" placeholder="Enter Product Description"
                        wire:model.defer="description"></textarea>
                    @error('description')
                        <span class="text-danger error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-6">
                    <div class="mt-3">
                        {{-- <label for="featured" class="form-label">Featured</label> --}}
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" id="" value="true"
                                    wire:model.defer='featured' /> Featured
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" id="" value="false"
                                    wire:model.defer="featured" />
                                Non-Featured
                            </label>
                        </div>

                        @error('featured')
                            <span class="text-danger error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="category" class="form-label">Category</label>
                        <input class="form-control" id="category" placeholder="Enter Product Category" require
                            wire:model.defer="category" />
                        @error('category')
                            <span class="text-danger error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                <div class="col-8">
                    {{-- <input type="file" class="form-control" name="fileName" wire:model.defer="fileName" /> --}}
                    {{-- <div wire:loading wire:target="fileName">Uploading...</div> --}}
                    {{-- @error('fileName')
                                        <span class="text-danger error">{{ $message }}</span>
                                    @enderror --}}
                </div>
                <div class="col-4">
                    {{-- @if ($fileName)
                                        Image Preview:
                                        <img src="{{ $fileName->temporaryUrl() }}" width="140" height="150" wire:ignore />
                                    @endif --}}
                    {{-- <img src="" id='preview'> --}}
                </div>
            </div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save </button>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        const imageInput = function() {
            return {
                set: async function(file) {
                    livewire.emit('fileSelected', await this.convertBase64(file));
                },

                convertBase64(file) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = error => reject(error);
                    })
                }
            }
        }
    </script>
@endpush
