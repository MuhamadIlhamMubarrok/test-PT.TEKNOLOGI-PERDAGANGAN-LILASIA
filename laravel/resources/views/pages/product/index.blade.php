@extends('dashboard')

@section('title', 'Product')

@section('content')

    @push('style')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css"
            integrity="sha512-EZSUkJWTjzDlspOoPSpUFR0o0Xy7jdzW//6qhUkoZ9c4StFkVsp9fbbd0O06p9ELS3H486m4wmrCELjza4JEog=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endpush

    <div class="page-content">

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Product</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success" id="success-alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger" id="error-alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <a href="{{ route('products.create') }}" class="btn btn-primary mb-3">Create Client</a>

                        <!-- Search Form -->
                        <form action="{{ route('products.index') }}" method="GET" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search Product"
                                    value="{{ old('search', $search) }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </div>
                            </div>
                        </form>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Gambar</th>
                                    <th>Nama</th>
                                    <th>Harga</th>
                                    <th>Deskripsi</th>
                                    <th>
                                        @if (in_array('admin', $roles))
                                            Action
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $index => $product)
                                    <tr>
                                        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                                        <td><img src="{{ asset('storage/productImage/' . $product->image) }}" alt="Gambar"
                                                width="50"></td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ number_format($product->price, 2) }}</td>
                                        <td>{{ $product->description }}</td>
                                        <td>
                                            @if (in_array('admin', $roles))
                                                <a href="{{ route('products.edit', $product->id) }}"
                                                    class="btn btn-warning">Edit</a>
                                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>


                        <!-- Pagination Links -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $products->appends(['search' => $search])->links('pagination.simple-bootstrap-5') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#success-alert").fadeTo(2000, 500).slideUp(500, function() {
                $(this).slideUp(500);
            });
            $("#error-alert").fadeTo(2000, 500).slideUp(500, function() {
                $(this).slideUp(500);
            });
        });
    </script>
@endpush
