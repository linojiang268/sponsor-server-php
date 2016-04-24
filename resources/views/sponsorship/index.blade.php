@extends('layouts.main')
@section('title', '我的赞助')

@section('scripts')
    <script type="text/javascript" src="/scripts/jquery.twbsPagination.min.js"></script>
    <script type="text/javascript">
        function loadSponsorships(page) {
            $.get('/web/sponsorships/page/' + page, function(resp) {
                if (resp.code != 0) {
                    // notify user that error happens
                    return;
                }

                var $table = $('#tblSponsorships'),
                    sponsorships = resp.sponsorships;
                var $container = $('tbody', $table);
                $container.html(''); // clear old data
                if (sponsorships && sponsorships.length) {
                    for (var i = 0, n = sponsorships.length; i < n; i++) {
                        var sponsor = sponsorships[i];
                        var html = '<tr>';
                            html += '<td><a href="/web/sponsorships/' + sponsor.id + '">' + sponsor.name + '</a></td>';
                            html += '<td>' + sponsor.application_start_date + ' - ' + sponsor.application_end_date + '</td>';
                            html += '<td>' + sponsor.intro + '</td>';
                            html += '<td>' + sponsor.application_condition + '</td>';
                            if(sponsor.status == '0'){
                                html += '<td>未发布</td>';
                            }
                            if(sponsor.status == '1'){
                                html += '<td>已发布</td>';
                            }
                            if(sponsor.status == '2') {
                                html += '<td>已关闭</td>';
                            }

                            html += '</tr>';
                        console.log(html);
                        $(html).appendTo($container);
                    }
                } else {
                    $('<tr><td colspan="5"><span class="text-info">您还没有发起过赞助</span></td></tr>').appendTo($container);
                }

                if (resp.totalPages > 0) {
                    $('#pager').twbsPagination({
                        totalPages: resp.totalPages,
                        visiblePages: 5,
                        onPageClick: function (event, page) {
                            loadSponsorships(page);
                        }
                    });
                }
            }, 'json');
        }

        loadSponsorships(1);
    </script>
@endsection

@section('content')
    <div class="page-header">
        <h3>我的赞助
            <div class="pull-right small">
                <a href="/web/sponsorships/create" class="btn btn-primary">
                    <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"> 发起赞助 </span>
                </a>
            </div>
        </h3>
    </div>

    <table class="table table-striped" id="tblSponsorships">
        <thead>
            <tr>
                <th>赞助名</th>
                <th>申请日期</th>
                <th>描述</th>
                <th>申请条件</th>
                <th>状态</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <div id="pager"></div>
@endsection