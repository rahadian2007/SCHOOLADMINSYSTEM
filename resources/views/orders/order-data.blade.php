<div class="row">
    <div class="col-sm-12 col-lg-4">
        <div class="d-flex justify-content-center">
            <img src="/svg/illustration-dashboard.svg" width="220" />
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card text-white bg-info">
        <div class="card-body pb-0">
            <div class="text-value-xl">@currency($commissionsToday)</div>
            <div>Bagi Hasil Hari Ini</div>
        </div>
        <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
            <canvas class="chart" height="70"></canvas>
        </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card text-white bg-warning">
        <div class="card-body pb-0">
            <div class="text-value-xl">@currency($salesToday)</div>
            <div>Omzet Hari Ini</div>
        </div>
        <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
            <canvas class="chart" height="70"></canvas>
        </div>
        </div>
    </div>
</div>