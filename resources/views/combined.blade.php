@extends('layouts.app')

@section('header')
	@include('layouts.header')
@stop

@section('sidebar_header')
	@include('layouts.sidebar_header')
@stop

@section('sidebar')
	@include('layouts.sidebar')
@stop

@section('body')
	@include($view)
@stop

@section('footer')
	@include('layouts.footer')
@stop
