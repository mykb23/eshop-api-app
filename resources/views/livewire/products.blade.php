<div>
    @include('livewire.create')
    @include('livewire.update')
    @if (session()->has('message'))
        <div class="alert alert-success" role="alert" style="margin-top:30px;">
            {{ session('message') }}
        </div>
    @endif
    <div class="row">
        @foreach ($products as $product)
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->title }}</hh5>
                            <p class="card-text">{{ $product->price }} </p>
                            <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
