<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\Modal;

/* @var $model app\models\Contest */
/* @var $rankResult array */

$problems = $model->problems;
$first_blood = $rankResult['first_blood'];
$result = $rankResult['rank_result'];
$submit_count = $rankResult['submit_count'];
?>
<?php if ($model->isScoreboardFrozen()): ?>
<p>现已是封榜状态，榜单将不再实时更新，待赛后再揭晓</p>
<?php endif; ?>
<table class="table">
    <thead>
        <tr>
            <th style="width:2.5rem">Rank</th>
            <th style="width:8rem">Number</th>
            <th>Who</th>
            <th style="min-width:6.4rem" title="solved / penalty time">Score</th>
            <?php foreach($problems as $key => $p): ?>
            <th style="min-width:6.4rem">
                <?= Html::a(chr(65 + $key), ['/contest/problem', 'id' => $model->id, 'pid' => $key], ['class' => 'text-dark']) ?>
                <small>
                    <?php
                    // echo "(";
                    if (isset($submit_count[$p['problem_id']]['solved']))
                        echo $submit_count[$p['problem_id']]['solved'];
                    else
                        echo 0;
                    echo " / ";
                    if (isset($submit_count[$p['problem_id']]['submit']))
                        echo $submit_count[$p['problem_id']]['submit'];
                    else
                        echo 0;
                    // echo ")";
                    ?>
                </small>
            </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php for ($i = 0, $ranking = 1; $i < count($result); $i++): ?>
        <?php $rank = $result[$i]; ?>
        <tr>
            <td>
                <?php
                //线下赛，参加比赛但不参加排名的处理
                if ($model->scenario == \app\models\Contest::SCENARIO_OFFLINE && $rank['role'] != \app\models\User::ROLE_PLAYER) {
                    echo '*';
                }
                elseif ($rank['role'] == \app\models\User::ROLE_ADMIN) {
                    echo '*';
                } else {
                    echo $ranking;
                    $ranking++;
                }
                ?>
            </td>
            <td>
                <?= Html::encode($rank['student_number']); ?>
            </td>
            <td>
                <?= Html::a(Html::encode($rank['nickname']), ['/user/view', 'id' => $rank['user_id']], ['class' => 'text-dark']) ?>

            </td>
            <td>
                <span class="font-weight-bold"><?= $rank['solved'] ?></span> 
                <small class="text-secondary">
                <?php if (intval($rank['time'] / 60) < 100000): ?>
                (<?= intval($rank['time'] / 60) ?>)
                <?php else:?>
                (10W+)
                <?php endif; ?>
                </small>
            </td>
            <?php
            foreach($problems as $key => $p) {
                $css_class = '';
                $num = '';
                $time = '';
                if (isset($rank['ac_time'][$p['problem_id']]) && $rank['ac_time'][$p['problem_id']] != -1) {
                    if ($first_blood[$p['problem_id']] == $rank['user_id']) {
                        $css_class = 'text-primary font-weight-bold';
                    } else {
                        $css_class = 'text-success font-weight-bold';
                    }
                    if ($rank['wa_count'][$p['problem_id']] == 0) {
                        $num = '+';
                    }
                    else{
                        $num = '+' . $rank['wa_count'][$p['problem_id']];
                    }
                    if (intval($rank['ac_time'][$p['problem_id']]) < 100000){
                        $time = ' <small class="text-secondary">(' . intval($rank['ac_time'][$p['problem_id']]) . ')</small>';
                    }
                    else{
                        $time = ' <small class="text-secondary">(' . '10W+' . ')</small>';
                    }
                    
                } 
                // else if (isset($rank['pending'][$p['problem_id']]) && $rank['pending'][$p['problem_id']]) {
                //     $num = $rank['wa_count'][$p['problem_id']] + $rank['pending'][$p['problem_id']];
                //     $css_class = 'text-secondary';
                //     $time = '';
                // } 
                else if (isset($rank['wa_count'][$p['problem_id']])) {
                    $css_class = 'text-danger font-weight-bold';
                    if($rank['wa_count'][$p['problem_id']] != 0) {
                        $num = '-' . $rank['wa_count'][$p['problem_id']];
                    }
                    $time = '';
                }
                // 封榜的显示
                if ($model->isScoreboardFrozen() && isset($rank['pending'][$p['problem_id']]) && $rank['pending'][$p['problem_id']]) {
                    if ($rank['wa_count'][$p['problem_id']] != 0) {
                        $num = "-" . $rank['wa_count'][$p['problem_id']] . " <span class=\"text-dark\">/<span> <span class=\"text-warning\">" .  $rank['pending'][$p['problem_id']] . "<span>";
                    } else {
                        $num = "<span class=\"text-warning\">" .  $rank['pending'][$p['problem_id']] . "<span>";
                    }
                }
                if ((!Yii::$app->user->isGuest && $model->created_by == Yii::$app->user->id) || (!$model->isScoreboardFrozen() && $model->isContestEnd())) {
                    $url = Url::toRoute([
                        '/contest/submission',
                        'pid' => $p['problem_id'],
                        'cid' => $model->id,
                        'uid' => $rank['user_id']
                    ]);
                    echo "<td class=\"{$css_class}\" style=\"cursor:pointer\" data-click='submission' data-href='{$url}'>{$num}{$time}</td>";
                } else {
                    echo "<td class=\"{$css_class}\">{$num}{$time}</td>";
                }
            }
            ?>
            </td>
        </tr>
        <?php endfor; ?>
    </tbody>
</table>
<?php
$js = "
$('[data-click=submission]').click(function() {
    $.ajax({
        url: $(this).attr('data-href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
            $('#submission-content').html(html);
            $('#submission-info').modal('show');
        }
    });
});
";
$this->registerJs($js);
?>
<?php Modal::begin([
    'options' => ['id' => 'submission-info']
]); ?>
<div id="submission-content">
</div>
<?php Modal::end(); ?>