@if (count($errors) > 0)
<a id="errors"></a>
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@section('page-specific-js')
<script>
    window.location.hash = "errors";
</script>
@stop
@endif
