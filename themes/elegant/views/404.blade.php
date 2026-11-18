@extends('themes.elegant.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="error-page py-5">
                <h1 class="error-code display-1 fw-light text-primary">404</h1>
                <h2 class="error-title display-5 fw-light mb-4">Page Not Found</h2>
                <p class="error-description fs-5 text-muted mb-4">
                    Oops! The page you're looking for has moved or doesn't exist.
                </p>
                
                <div class="error-actions mb-5">
                    <a href="{{ url('/') }}" class="btn btn-outline-primary btn-lg mx-2">
                        Go Home
                    </a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary btn-lg mx-2">
                        Contact Us
                    </a>
                </div>

                <div class="search-form-container">
                    <form action="{{ url('/search') }}" method="GET" class="d-flex flex-column flex-md-row gap-2 justify-content-center">
                        <input type="text" name="q" class="form-control form-control-lg" placeholder="Search for content..." required style="max-width: 400px;">
                        <button type="submit" class="btn btn-outline-primary btn-lg">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
