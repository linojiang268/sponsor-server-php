@extends('layouts.main')
@section('title', '发布赞助')

@section('styles')
    <link href="/css/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection

@section('scripts')
    <script type="text/javascript" src="/scripts/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="/scripts/bootstrap-datepicker.zh-CN.min.js"></script>
    <script type="text/javascript" src="/scripts/sponsorship.js"></script>
    <script type="text/javascript">
        $(function() {
            $('#from_date').datepicker({
                language:'zh-CN',
                todayHighlight: 1,
                autoclose: 1,
                format:"yyyy-mm-dd"
            });
            $('#to_date').datepicker({
                language:'zh-CN',
                todayHighlight: 1,
                autoclose: 1,
                format:"yyyy-mm-dd"
            });
        });
    </script>
@endsection

@section('content')

    <div class="page-header">
        <h3>发布赞助
            <div class="pull-right small">
                <a href="javascript:{}" id="goBack" class="btn btn-primary">
                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> 返回列表 </span>
                </a>
            </div>
        </h3>
    </div>

    {{--<div  class="modal fade" id="divStoreError" tabindex="-1" role="dialog" style="margin: 8px;top: 50%; z-index: 9; display: none">--}}
        {{--<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>--}}
        {{--<span class="message"></span>--}}
    {{--</div>--}}


    <div class="modal fade"  id="divStoreError" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">错误</h4>
                </div>
                <div class="modal-body text-danger">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <span class="message"></span>                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="" style="margin-top: 15px">
        <form action="/web/sponsorships" id="login-form" method="post" class="form">
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label" for="name">赞助标题</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="赞助标题" />
                </div>
                <div class="form-group">
                    <label class="control-label" style="display: block;" for="date">赞助申请周期</label>
                    <input style="display: inline;width: 200px;" id="from_date" name="application_start_date" data-date="2015-12-08" data-date-format="yyyy-mm-dd" data-link-format="yyyy-mm-dd" class="form-control" placeholder="YYYY-MM-DD" type="text" readonly="readonly" />
                    <label style="display: inline;" class="control-label" for="text">至</label>
                    <input style="display: inline;width: 200px;" id="to_date" name="application_end_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="end" data-link-format="yyyy-mm-dd" class="form-control" placeholder="YYYY-MM-DD" type="text" readonly="readonly" />
                </div>

                <div class="form-group">
                    <label class="control-label" for="introduction">赞助介绍</label>
                    <textarea id="sponsor_intro" name="intro" class="form-control" placeholder="赞助介绍" ></textarea>
                </div>

                <div class="form-group">
                    <label class="control-label" for="applicant_intro">赞助申请要求</label>
                    <textarea id="applicant_intro" name="application_condition" class="form-control" placeholder="赞助申请要求" ></textarea>
                </div>
                <input id="status" name="status" value="0" type="hidden" />
                {!! csrf_field() !!}
            </div>

            <div class="modal-footer">
                <span id="divStoreIndicator" style="display: none;"></span>
                <button type="button" id="btnCancel" class="btn btn-default" data-dismiss="modal">取消</button>
                <div class="btn-group dropup">
                    <button id="btnStorePublish" type="button" class="btn btn-primary">发布赞助</button>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:{}" id="btnStore">保存草稿</a></li>
                    </ul>
                </div>
            </div>


        </form>
    </div>

@endsection