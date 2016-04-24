@extends('layouts.main')
@section('title', 'Sponsorship')

@section('styles')
    <link href="/css/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection

@section('scripts')
    <script type="text/javascript" src="/scripts/jquery.twbsPagination.min.js"></script>
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
    @if (!$sponsorship)
    No such sponsorship
    @else
        @if ($sponsorship->status == 1 || $sponsorship->status == 2)
            <?php $disabled = 'disabled="disabled"'; ?>
        @else
            <?php $disabled = ''; ?>
        @endif
        <input name="sponsorshipId" id="sponsorshipId" value="{{ $sponsorship->id}}" type="hidden" />

        <div class="page-header">
            <h3>{{ $sponsorship->name  }}
                <div class="pull-right small">
                    <a href="/web/sponsorships" class="btn btn-primary">
                        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> 返回列表 </span>
                    </a>
                </div>
            </h3>
        </div>

        <div>
            <ul class="nav nav-tabs">
                <li role="presentation" class="active" id="btnDetail" ><a href="javascript:{}">详情</a></li>
                @if ($sponsorship->status == 1 || $sponsorship->status == 2)
                    <li role="presentation" id="btnApplicationList" ><a href="javascript:{}" >申请列表</a></li>
                    {{--<li role="presentation"><a href="javascript:{}"  id="btnDashboard">数据统计</a></li>--}}
                @endif
            </ul>
        </div>

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

        <div id="detail" style="display: block;">
            <form action="/web/sponsorships" id="detail-form" method="post" class="form">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label" for="name">赞助标题</label>
                        <input type="text" id="name" name="name" value="{{ $sponsorship->name}}" class="form-control" placeholder="赞助标题" {{ $disabled }} />
                    </div>
                    <div class="form-group">
                        <label class="control-label" style="display: block;" for="date">赞助申请周期</label>
                        <input style="display: inline;width: 200px;" id="from_date" name="application_start_date" value="{{ $sponsorship->application_start_date}}" data-date="{{ $sponsorship->application_start_date}}"  data-date-format="yyyy-mm-dd" data-link-format="yyyy-mm-dd" class="form-control" placeholder="YYYY-MM-DD" type="text"  {{ $disabled }} readonly="readonly" />
                        <label style="display: inline;" class="control-label" for="text">至</label>
                        <input style="display: inline;width: 200px;" id="to_date" name="application_end_date" value="{{ $sponsorship->application_end_date}}" data-date="{{ $sponsorship->application_end_date}}" data-date-format="yyyy-mm-dd" data-link-field="end" data-link-format="yyyy-mm-dd" class="form-control" placeholder="YYYY-MM-DD" type="text" readonly="readonly" @if ($sponsorship->status == 2) disabled="disabled" @endif  />
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="introduction">赞助介绍</label>
                        <textarea id="sponsor_intro" name="intro" class="form-control" placeholder="赞助介绍" {{ $disabled }} >{{ $sponsorship->intro}}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="applicant_intro">赞助申请要求</label>
                        <textarea id="applicant_intro" name="application_condition" class="form-control" placeholder="赞助申请要求" {{ $disabled }} >{{ $sponsorship->application_condition}}</textarea>
                    </div>

                    @if ($sponsorship->status == 0)
                        <input id="status" name="status" value="－1" type="hidden" />
                    @endif
                    <input name="id" value="{{ $sponsorship->id}}" type="hidden" />
                    {!! csrf_field() !!}
                </div>

                @if ($sponsorship->status < 2)
                    <div class="modal-footer">
                        <span id="divStoreIndicator" style="display: none;"></span>
                        <div class="btn-group dropup">
                            @if ($sponsorship->status == 0)
                                <button id="btnPublish" type="button" class="btn btn-primary">发布赞助</button>
                            @endif
                            @if ($sponsorship->status == 1)
                                <button id="btnDelay" type="button" class="btn btn-primary">赞助延期</button>
                            @endif
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    @if ($sponsorship->status == 0)
                                        <a href="javascript:{}" id="btnUpdate">修改赞助</a>
                                        <a href="javascript:{}" id="btnDelete">删除赞助</a>
                                    @endif
                                    @if ($sponsorship->status == 1)
                                        <a href="javascript:{}" id="btnClose">关闭赞助</a>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif

            </form>
        </div>

        <div id="dashboard" style="display: none;">

        </div>


        <div id="applicationList" style="display: none;">
            <table class="table table-striped" id="tblSponsorships">
                <thead>
                <tr>
                    <th>社团名称</th>
                    <th>联系人</th>
                    <th>手机号</th>
                    <th>申请描述</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="pager"></div>
            {!! csrf_field() !!}
        </div>
    @endif


@endsection