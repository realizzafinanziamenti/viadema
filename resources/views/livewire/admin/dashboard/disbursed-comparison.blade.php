<x-dashboard.dashboard-card class="col-span-24 xl:col-span-11 h-[300px] flex flex-col" header="Liquidato">

    {{-- Up --}}
    <div class="flex items-center justify-end gap-8 py-0.5 px-8 text-sm text-gray-custom-5">
        <div class="flex items-center gap-1.5">
            <div class="h-3.5 w-3.5 bg-blue-custom rounded-full"></div>
            {{ $this->lastMonthName }}
        </div>

        <div class="flex items-center gap-1.5">
            <div class="h-3.5 w-3.5 bg-purple-custom rounded-full"></div>
            {{ $this->currentMonthName }}
        </div>
    </div>

    <div class="flex items-center gap-5 p-1 flex-1">

        {{-- Left --}}
        <div class="w-1/4 h-full truncate">
            <div class="text-2xl font-bold text-black-custom truncate mt-1.5">{{ $this->currentMonthDisbursedFormatted }}
            </div>
            <div class="text-sm text-gray-custom-5">Liquidato {{ $this->currentMonthName }}</div>
            <div class="mt-2 w-[90px]">
                <x-dashboard.dashboard-button label="{{ $this->percentageComparison }}"
                    class="bg-green-custom text-white" />
            </div>
        </div>

        {{-- Right --}}
        <div class="w-3/4 h-full relative">
            <div class="absolute inset-0">
                <canvas id="comparisonDisbursedChart" class="w-full h-full"></canvas>
            </div>
        </div>

    </div>

    @script
        <script>
            const ctx = document.getElementById('comparisonDisbursedChart').getContext('2d');

            const assignmentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($this->disbursedChartLabels),
                    datasets: [{
                        data: @json(array_values($this->disbursedChartLastValues)),
                        borderColor: '#004CA4',
                        borderWidth: 1,
                        tension: 0.4,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#004CA4',
                        pointBorderWidth: 1,
                    }, {
                        data: @json(array_values($this->disbursedChartCurrentValues)),
                        borderColor: '#806BFF',
                        borderWidth: 1,
                        tension: 0.4,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#806BFF',
                        pointBorderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grace: '10%',
                            ticks: {
                                // formatta valori asse y (esempio 5k, 10k, 15k, etc.)
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return (value / 1000) + 'k';
                                    }
                                    return value;
                                }
                            }
                        }
                    }
                },
            });
        </script>
    @endscript
</x-dashboard.dashboard-card>
