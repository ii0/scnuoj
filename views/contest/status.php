<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap4\Modal;
use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $searchModel app\models\SolutionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $data array */

$this->title = $model->title;
$this->params['model'] = $model;
$problems = $model->problems;
$problems_size = sizeof($problems);

$nav = [];
$nav[''] = '请选择';
foreach ($problems as $key => $p) {
    $nav[$p['problem_id']] = ($problems_size > 26)
        ? ('P' . str_pad($key + 1, 2, '0', STR_PAD_LEFT))
        : chr(65 + $key);

    $nav[$p['problem_id']] .=  '. ' . $p['title'];
}
$userInContest = $model->isUserInContest();
$isContestEnd = $model->isContestEnd();
?>
<div class="solution-index">
    <?php if (Yii::$app->setting->get('isContestMode')) : ?>
    <?php elseif ($model->isContestEnd() && $model->isScoreboardFrozen()) : ?>
        <div class="alert alert-light" style="text-align: left !important;"><i class="fas fa-fw fa-info-circle"></i> 比赛已经结束，封榜状态尚未解除，请等候管理员滚榜或解榜。</div>
        <p></p>
    <?php elseif ($model->isScoreboardFrozen()) : ?>
        <div class="alert alert-light" style="text-align: left !important;"><i class="fas fa-fw fa-info-circle"></i> 现已是封榜状态，只显示封榜前的提交及您个人的所有提交记录。</div>
    <?php endif; ?>
    <?php if ($model->type != Contest::TYPE_OI || $isContestEnd) : ?>
        <?= $this->render('_status_search', ['model' => $searchModel, 'nav' => $nav, 'contest_id' => $model->id]); ?>
    <?php endif; ?>

    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'table-responsive'],
        'rowOptions' => ['class' => ' animate__animated animate__fadeIn animate__faster'],
        'tableOptions' => ['class' => 'table'],
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->id, ['/solution/detail', 'id' => $model->id], ['target' => '_blank', 'class' => 'text-dark']);
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:90px;']
            ],
            [
                'attribute' => 'who',
                'value' => function ($model, $key, $index, $column) {
                    if (isset($model->user)) {
                        return Html::a($model->user->nickname, ['/user/view', 'id' => $model->created_by], ['class' => 'text-dark']);
                    }
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:150px;']
            ],
            [
                'label' => Yii::t('app', 'Problem'),
                'value' => function ($model, $key, $index, $column) use ($problems_size) {

                    $res = $model->getProblemInContest();
                    if (!isset($model->problem)) {
                        return null;
                    }
                    if (!isset($res->num)) {
                        return $model->problem->title;
                    } else {
                        $cur_id = ($problems_size > 26)
                            ? ('P' . str_pad($res->num + 1, 2, '0', STR_PAD_LEFT))
                            : chr(65 + $res->num);
                    }
                    return Html::a(
                        $cur_id . ' - ' . $model->problem->title,
                        ['/contest/problem', 'id' => $res->contest_id, 'pid' => $res->num],
                        ['class' => 'text-dark']
                    );
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:200px;']
            ],
            [
                'attribute' => 'result',
                'value' => function ($solution, $key, $index, $column) use ($model, $userInContest, $isContestEnd) {
                    // OI 比赛模式未结束时不返回具体结果
                    if ($model->type == Contest::TYPE_OI && !$isContestEnd) {
                        return Yii::t('app', 'Pending');
                    }
                    $otherCan = ($isContestEnd && Yii::$app->setting->get('isShareCode'));
                    $createdBy = (!Yii::$app->user->isGuest && ($model->created_by == Yii::$app->user->id || Yii::$app->user->id == $solution->created_by));
                    return $solution->getResult();
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:90px;']
            ],
            [
                'attribute' => 'score',
                'enableSorting' => false,
                'visible' => $model->type == Contest::TYPE_IOI || $model->type == Contest::TYPE_HOMEWORK ||
                    ($model->type == Contest::TYPE_OI && $isContestEnd),
                'headerOptions' => ['style' => 'min-width:90px;']
            ],
            [
                'attribute' => 'time',
                'value' => function ($solution, $key, $index, $column) use ($model, $isContestEnd) {
                    // OI 比赛模式未结束时不返回具体结果
                    if ($model->type == \app\models\Contest::TYPE_OI && !$isContestEnd) {
                        return "－";
                    }
                    return $solution->time . ' MS';
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:90px;']
            ],
            [
                'attribute' => 'memory',
                'value' => function ($solution, $key, $index, $column) use ($model, $isContestEnd) {
                    // OI 比赛模式未结束时不返回具体结果
                    if ($model->type == \app\models\Contest::TYPE_OI && !$isContestEnd) {
                        return "－";
                    }
                    return $solution->memory . ' KB';
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:90px;']
            ],
            [
                'attribute' => 'language',
                'value' => function ($solution, $key, $index, $column) use ($model, $isContestEnd) {
                    $otherCan = ($isContestEnd && Yii::$app->setting->get('isShareCode'));
                    if ($solution->canViewSource() || $otherCan) {
                        return Html::a(
                            $solution->getLang(),
                            ['/solution/source', 'id' => $solution->id],
                            ['onclick' => 'return false', 'data-click' => "solution_info", 'class' => 'text-dark']
                        );
                    } else {
                        return $solution->getLang();
                    }
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:90px;']
            ],
            [
                'attribute' => 'code_length',
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:90px;']
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model, $key, $index, $column) {
                    return Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['title' => $model->created_at]);
                },
                'format' => 'raw',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'min-width:90px;']
            ]
        ],
        'pager' => [
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]
    ]); ?>
    <?php
    $url = \yii\helpers\Url::toRoute(['/solution/verdict']);
    $loadingImgUrl = Yii::getAlias('@web/images/loading.gif');
    $js = <<<EOF
$('[data-click=solution_info]').click(function() {
    $.ajax({
        url: $(this).attr('href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
            $('#solution-content').html(html);
            $('#solution-info').modal('show');
        }
    });
});
function updateVerdictByKey(submission) {
    $.get({
        url: "{$url}?id=" + submission.attr('data-submissionid'),
        success: function(data) {
            var obj = JSON.parse(data);
            submission.attr("waiting", obj.waiting);
            submission.text(obj.result);
            if (obj.verdict === 4) {
                submission.attr("class", "text-success")
            }
            if (obj.waiting === "true") {
                submission.append('<img src="{$loadingImgUrl}" alt="loading">');
            }
        }
    });
}
var waitingCount = $("strong[waiting=true]").length;
if (waitingCount > 0) {
    console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
    var interval = null;
    var waitingQueue = [];
    $("strong[waiting=true]").each(function(){
        waitingQueue.push($(this));
    });
    waitingQueue.reverse();
    var testWaitingsDone = function () {
        updateVerdictByKey(waitingQueue[0]);
        var waitingCount = $("strong[waiting=true]").length;
        while (waitingCount < waitingQueue.length) {
            if (waitingCount < waitingQueue.length) {
                waitingQueue.shift();
            }
            if (waitingQueue.length === 0) {
                break;
            }
            updateVerdictByKey(waitingQueue[0]);
            waitingCount = $("strong[waiting=true]").length;
        }
        console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
        
        if (interval && waitingCount === 0) {
            console.log("Stopping submissionsEventCatcher.");
            clearInterval(interval);
            interval = null;
        }
    }
    interval = setInterval(testWaitingsDone, 1000);
}
EOF;
    $this->registerJs($js);
    ?>
</div>
<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
<div id="solution-content">
</div>
<?php Modal::end(); ?>