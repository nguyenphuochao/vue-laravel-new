@extends('layouts.app');

@section('content')
<script>
  window.__user__ = @json($user);
</script>
@endsection
