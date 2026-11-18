@extends('themes.rosetta.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="error-code">404</h1>
                <h2 class="error-title">Page Not Found</h2>
                <p class="error-description">
                    Oops! The page you're looking for has moved or doesn't exist.
                </p>
                
                <div class="error-actions">
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        Go Home
                    </a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary">
                        Contact Us
                    </a>
                </div>

                <div class="search-form-container mt-4">
                    <form action="{{ url('/search') }}" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control me-2" placeholder="Search for content..." required>
                        <button type="submit" class="btn btn-primary">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
