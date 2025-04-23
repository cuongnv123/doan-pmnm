@extends('admin.layouts.app')
@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Color</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="#" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <form action="" id="colorForm" name="colorForm" method="post">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_id">Tên sản phẩm</label>
                                    <select name="product_id" id="product_id" class="form-control">
                                        <option value="">-- Chọn sản phẩm --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ $color->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p></p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Tên màu</label>
                                    <select name="name" id="name" class="form-control">
                                        <option value="">-- Chọn màu --</option>
                                        <option value="blue" {{ $color->name == 'blue' ? 'selected' : '' }}>blue</option>
                                        <option value="white" {{ $color->name == 'white' ? 'selected' : '' }}>white</option>
                                        <option value="black" {{ $color->name == 'black' ? 'selected' : '' }}>black</option>
                                        <option value="gray" {{ $color->name == 'gray' ? 'selected' : '' }}>gray</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                   
                </div>
                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('colors.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </section>
@endsection

@section('customJs')
    <script>
        $("#colorForm").submit(function(event) {
            event.preventDefault();
            var element = $(this);
            $("button[type=submit]").prop('disabled', true);

            $.ajax({
                url: '{{ route('colors.update', $color->id) }}',
                type: 'put',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);
                    if (response["status"] == true) {

                        window.location.href = "{{ route('colors.index') }}";

                    }
                },
            });

        });
    </script>
@endsection
