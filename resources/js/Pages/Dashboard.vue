<script>
import useTranslateText from "@/Composables/useTranslateText.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import axios from "axios";
import {Head} from "@inertiajs/vue3";
import VueApexCharts from "vue3-apexcharts";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import useFormatName from "@/Composables/useFormatName.js";

export default {
    name: "Dashboard",
    components: {
        ButtonPrimary,
        Head,
        apexCharts: VueApexCharts,
        useTranslateText,
        useFormatName,
    },
    data() {
        return {
            categorySelected: 'all',
            arrayCategory: ['toner', 'paper', 'form', 'cartridge', 'ribbon', 'desk', 'others', 'all'],
            toRouteSectorComparison: this.authUser.level + '/order/sector/comparison/get',
            toRouteTopProductsAndUsers: this.authUser.level + '/order/ranking/product/requester/get',
            loading: true,
            loading2: true,
            startAt: '',
            endAt: '',
            topStartAt: '',
            topEndAt: '',
            chartOptionsPolar: {
                chart: {
                    type: 'polarArea',
                },
                stroke: {
                    colors: ['#fff']
                },
                fill: {
                    opacity: 0.9
                },
            },
            seriesPolarData: [],
            chartOptionsRequesterTop: {
                colors: ['rgba(0, 227, 150, 0.9)'],
                chart: {
                    height: 250,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        columnWidth: '25%',
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val; // Remove o "%" do valor
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                    }
                },
                yaxis: {
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        show: false,
                        formatter: function (val) {
                            return val;
                        }
                    }
                },
            },
            seriesRequesterTop: [{
                name: 'Total Consumo',
            }],
            chartOptionsProductTop: {
                colors: ['rgba(254, 176, 25, 0.9)'],
                chart: {
                    height: 250,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        columnWidth: '25%',
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                    }
                },
                yaxis: {
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        show: false,
                        formatter: function (val) {
                            return val;
                        }
                    }
                },
            },
            seriesProductTop: [{
                name: 'Total Consumo',
            }],
        }
    },
    props: {
        authUser: {
            type: Object,
            required: true
        }
    },
    async mounted() {
        this.toast = useSwalAlert({}, 1500);
        this.getTopProductsAndUsers();
        this.getSectorComparison();
    },
    methods: {
        useTranslateText,
        async getSectorComparison() {
            this.loading2 = true;
            try {
                const response = await axios.get(this.toRouteSectorComparison, {
                    params: {
                        category: this.$refs.form_category.value,
                        start_at: this.$refs.form_start.value,
                        end_at: this.$refs.form_end.value
                    }
                });
                if (!response.data.data) {
                    this.toast.fire({
                        icon: "warning",
                        title: response.data.message
                    });
                    return;
                }
                // Atualize labels e seriesPolarData
                this.chartOptionsPolar.labels = response.data.data.sectors_usages.map(item => item.sector);
                this.seriesPolarData = response.data.data.sectors_usages.map(item => parseFloat(item.total_consumption));
                this.startAt = response.data.data.start_at;
                this.endAt = response.data.data.end_at;

                return true;
            } catch (errors) {
                // Se erro, lança toast
                const message = errors.response?.data?.message;
                let icon = 'error';
                if (message === 'Nenhum registro encontrado') {
                    icon = 'warning';
                }
                this.toast.fire({
                    icon: icon,
                    title: message
                });
                return false;
            } finally {
                this.loading2 = false;
            }
        },
        async getTopProductsAndUsers() {
            this.loading = true;
            try {
                const response = await axios.get(this.toRouteTopProductsAndUsers, {
                    params: {
                        start_at: this.$refs.top_form_start.value,
                        end_at: this.$refs.top_form_end.value
                    }
                });
                if (!response.data.data) {
                    this.toast.fire({
                        icon: "warning",
                        title: response.data.message
                    });
                    return;
                }

                this.chartOptionsRequesterTop.xaxis.categories = response.data.data.ranking_requester.map(item => useFormatName(item.name));
                const valuesRequesters = response.data.data.ranking_requester.map(item => parseFloat(item.total));
                let max = Math.max(...valuesRequesters) + 3;
                this.chartOptionsRequesterTop.yaxis.max = max;

                this.seriesRequesterTop = [{
                    name: 'Total Consumo',
                    data: valuesRequesters
                }];

                this.chartOptionsProductTop.xaxis.categories = response.data.data.ranking_product.map(item => item.name);
                const valuesProducts = response.data.data.ranking_product.map(item => parseFloat(item.total));
                max = Math.max(...valuesProducts) + 3;
                this.chartOptionsProductTop.yaxis.max = max;

                this.seriesProductTop = [{
                    name: 'Total Consumo',
                    data: valuesProducts
                }];
                this.topStartAt = response.data.data.start_at;
                this.topEndAt = response.data.data.end_at;

                return true;
            } catch (errors) {
                console.log(errors)
                // Se erro, lança toast
                const message = errors.response?.data?.message;
                let icon = 'error';
                if (message === 'Nenhum registro encontrado') {
                    icon = 'warning';
                }
                this.toast.fire({
                    icon: icon,
                    title: message
                });
                return false;
            } finally {
                // Finaliza o estado de carregamento da tabela
                this.loading = false;
            }
        }
    }
}
</script>

<template>
    <div class="content__content">
        <Head name="Dashboard"/>
        <div class="content">
            <div class="content__header"></div>
            <div class="content__main">
                <div class="dashboard__row">
                    <div class="graphic graphic__three">
                        <p class="graphic__title" v-if="loading">Top 3 consumo por usuário</p>
                        <div class="loader__container" v-if="loading">
                            <div class="loader"></div>
                        </div>
                        <p class="graphic__title"></p>
                        <div class="graphic--graphic" v-if="!loading">
                            <p class="graphic__title">Top 3 consumo por usuário</p>
                            <apex-charts
                                :width="'100%'"
                                :height="'250'"
                                type="bar"
                                :options="chartOptionsRequesterTop"
                                :series="seriesRequesterTop"
                                ref="requester_top"
                            />
                        </div>
                    </div>
                    <div class="graphic graphic__four">
                        <p class="graphic__title" v-if="loading">Top 3 consumo por produto</p>
                        <div class="loader__container" v-if="loading">
                            <div class="loader"></div>
                        </div>
                        <p class="graphic__title"></p>
                        <div class="graphic--graphic" v-if="!loading">
                            <p class="graphic__title">Top 3 consumo por produto</p>
                            <apex-charts
                                :width="'100%'"
                                :height="'250'"
                                type="bar"
                                :options="chartOptionsProductTop"
                                :series="seriesProductTop"
                                ref="product_top"
                            />
                        </div>
                    </div>
                    <div class="graphic__options graphic__options-one">
                        <div class="input__item">
                            <label for="start_at" class="form__label">Início</label>
                            <input ref="top_form_start" type="month" class="form__input form__input-custom"
                                   id="start_at" name="start_at" :value="topStartAt"/>
                        </div>
                        <div class="input__item">
                            <label for="end_at" class="form__label">Fim</label>
                            <input ref="top_form_end" type="month" class="form__input form__input-custom" id="end_at"
                                   name="end_at" :value="topEndAt"/>
                        </div>
                        <div class="input__item">
                            <ButtonPrimary name="Filtrar" @click="getTopProductsAndUsers"/>
                        </div>
                    </div>
                </div>
                <div class="dashboard__row">
                    <div class="graphic graphic__one">
                        <p class="graphic__title graphic__title--custom">Consumo de produtos por departamento</p>
                        <div class="graphic--graphic" v-if="!loading2">
                            <apex-charts
                                :width="'100%'"
                                :height="'450'"
                                ref="chart"
                                type="polarArea"
                                :options="chartOptionsPolar"
                                :series="seriesPolarData"
                            />
                        </div>
                        <div class="loader__container" v-if="loading2">
                            <div class="loader"></div>
                        </div>
                        <div class="graphic__options">
                            <div class="input__item">
                                <label for="start_at" class="form__label">Início</label>
                                <input ref="form_start" type="month" class="form__input form__input-custom"
                                       id="start_at" name="start_at" :value="startAt"/>
                            </div>
                            <div class="input__item">
                                <label for="end_at" class="form__label">Fim</label>
                                <input ref="form_end" type="month" class="form__input form__input-custom" id="end_at"
                                       name="end_at" :value="endAt"/>
                            </div>

                            <div class="input__item">
                                <label for="category" class="form__label">Categoria</label>
                                <select class="form__input" ref="form_category" name="category" id="category"
                                        v-model="categorySelected">
                                    <option v-for="opt in arrayCategory" :value="opt">
                                        {{ useTranslateText(opt, 'pt') }}
                                    </option>
                                </select>
                            </div>
                            <div class="input__item">
                                <ButtonPrimary name="Filtrar" @click="getSectorComparison"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content__footer"></div>
        </div>
    </div>
</template>

<style scoped>
@import "/resources/css/pages/dashboard.css";
</style>
