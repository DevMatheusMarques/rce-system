<script>
import {Head} from "@inertiajs/vue3";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import ButtonSecondary from "@/Components/ButtonSecondary.vue";
import ThTable from "@/Components/Table/ThTable.vue";
import axios from "axios";
import Pagination from "@/Components/Table/Pagination.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import ModalConfirm from "@/Components/ModalConfirm.vue";
import SearchBar from "@/Components/SearchBar.vue";
import TrashButton from "@/Components/TrashButton.vue";
import useTranslateText from "@/Composables/useTranslateText.js";
import UpdatePurchase from "@/Components/Purchases/UpdatePurchase.vue";
import useFormatDate from "@/Composables/useFormatDate.js";
import CloneOrder from "@/Components/Order/CloneOrder.vue";
import TableLoading from "@/Components/TableLoading.vue";
import RegisterOrder from "@/Components/Order/RegisterOrder.vue";
import UpdateOrder from "@/Components/Order/UpdateOrder.vue";
import useFormatName from "../Composables/useFormatName.js";


export default {
    name: "Order",
    components: {
        UpdateOrder,
        RegisterOrder,
        TableLoading,
        CloneOrder,
        UpdatePurchase,
        TrashButton,
        SearchBar,
        ModalConfirm,
        Dropdown, DropdownLink, Pagination, ThTable, ButtonSecondary, ButtonPrimary, Head
    },
    methods: {
        useFormatName,
        useTranslateText,
        useFormatDate,
        async getOrders() {
            //inicia carregamento da tabela
            this.loading = true;
            try {
                //requisição para recuperar os fornecedores
                const response = await axios.get(this.toRoute, {
                    params: {
                        per_page: this.perPage,
                        order_by: this.orderBy,
                        order_direction: this.orderDirection,
                        filters: this.filters
                    }
                });

                //atualiza fornecedores
                this.orders = response.data.data.data;
                //atualiza paginação
                this.pagination = response.data.data;

                if (response.data.data.total === 0) {
                    this.toast.fire({
                        icon: "warning",
                        title: response.data.message
                    });
                    return
                }
                return true
            } catch (errors) {
                //se erro, lança toast
                const message = errors.response.data.message;
                let icon = 'error';
                if (message === 'Nenhum registro encontrado') {
                    icon = 'warning';
                }
                this.toast.fire({
                    icon: icon,
                    title: message
                });
                return false
            } finally {
                //finaliza o estado de carregamento da tabela
                this.loading = false;
            }
        },
        orderItems(order) {
            //ordena os itens conforme necessidade
            //propriedade reativa para manter ordenação entre as requisições (ex: alterando entre as páginas)
            if (order) {
                this.orderBy = order.order_by;
                this.orderDirection = order.order_direction;
                this.getOrders();
            } else {
                this.orderBy = 'created_at';
                this.orderDirection = 'desc';
                this.getOrders();
            }
        },
        async changePage(url) {
            this.toRoute = url;
            await this.getOrders();
        },
        handleUpdateOrder(order) {
            //abrindo modal com pedido de compra selecionado
            this.order = order;
            this.updateOrderShow = true;
        },
        clearAllFilters() {
            const filters = this.filters;
            Object.keys(filters).forEach(key => {
                filters[key] = null;
            });
        },
        handleExportPDF(purchase) {
            window.location.href = this.authUser.level + '/purchase/export/pdf/' + purchase.id;
        },
        handleCloneOrder(order) {
            this.order = order;
            this.cloneOrderShow = true;
        },
        async handleSearch(val) {
            if (val === '') {
                return this.toast.fire({
                    icon: 'warning',
                    title: 'Insira algum valor para continuar.',
                });
            }
            this.clearAllFilters();
            this.filters.search = useTranslateText(val, 'en');
            this.toRoute = this.defaultRoute;
            await this.getOrders();
        },
        async handleFilterDate(event) {
            this.filters.created_at = event.target.value;
            await this.getOrders();
        },
        async clearFilterButton() {
            this.clearAllFilters()
            document.getElementById('search-input').value = '';
            document.querySelector('.form__input-date').value = '';
            await this.getOrders();
        },
        async updateStatus(data) {
            try {
                const endpointRoute = '/' + this.authUser.level + '/order/status/update';
                const response = await axios.put(endpointRoute, data);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                this.getOrders();
            } catch (errors) {
                data.element.value = data.status_old;
                this.errors = errors.response.data.message;
                this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        handleUpdateStatus(event, order) {
            const data = {
                id: order.id,
                status: event.target.value,
                status_old: order.status,
                element: event.target
            }
            this.updateStatus(data);
        },
        showOptions(order) {
            switch (order.status) {
                case 'in_progress':
                    return ['in_progress', 'completed', 'canceled'];
                case 'completed':
                    return ['completed'];
                case 'canceled':
                    return ['canceled'];
            }
        },
        disableOptions(order) {
            return order.status === 'canceled' || order.status === 'completed';
        },
    },
    async mounted() {//carragamento de componentes
        this.toast = useSwalAlert({}, 1500);
        await this.getOrders();
    },
    data() {
        return {
            orders: [],
            order: {},
            pagination: {},
            toRoute: this.authUser.level + '/order/get',
            registerPurchaseShow: false,
            updateOrderShow: false,
            cloneOrderShow: false,
            stateDropdown: true,
            loading: false,
            filters: {},
            orderBy: 'id',
            orderDirection: 'desc',
            perPage: 20,
            defaultRoute: this.authUser.level + '/order/get'
        }
    },
    props: {
        authUser: {
            type: Object,
            required: true
        }
    },
    computed: {
        isOperator() {
            return this.authUser.level === 'operator';
        },

    }
}
</script>

<template>
    <div class="content__content">
        <Head name="Orders"/>
        <RegisterOrder
            :visible="registerPurchaseShow"
            :auth-user="authUser"
            @close="registerPurchaseShow = false"
            @new-order="getOrders"
        />

        <UpdateOrder
            :order="order"
            :visible="updateOrderShow"
            :auth-user="authUser"
            @close="updateOrderShow = false"
            @update-order="getOrders"
        />
        <CloneOrder
            :order="order"
            :visible="cloneOrderShow"
            :auth-user="authUser"
            @close="cloneOrderShow = false"
            @new-order="getOrders"
         />
        <div class="content">
            <div class="content__header">
                <ButtonPrimary
                    name="Nova Saída"
                    @click="registerPurchaseShow = true"
                />

            </div>
            <div class="content__main">
                <div class="main__search">
                    <SearchBar @search="handleSearch"/>
                    <TrashButton @click="clearFilterButton"/>
                </div>
                <input class="form__input form__input-date" type="date" @change="handleFilterDate">
                <div class="main__abstract">
                </div>
                <div class="main__table">
                    <table class="table">
                        <thead class="table__thead">
                        <tr>
                            <ThTable th-id="id" @order="orderItems" :th-ordered="orders.length > 0">
                                ID
                            </ThTable>
                            <ThTable th-id="user_name" @order="orderItems" :th-ordered="orders.length > 0">
                                Responsável
                            </ThTable>
                            <ThTable th-id="requester_name" @order="orderItems" :th-ordered="false">
                                Solicitante
                            </ThTable>
                            <ThTable th-id="status" @order="orderItems" class="th__center"
                                     :th-ordered="orders.length > 0">
                                Status
                            </ThTable>
                            <ThTable th-id="items_count" @order="orderItems" class="th__center" :th-ordered="false">
                                Quantidade itens
                            </ThTable>
                            <ThTable th-id="created_at" @order="orderItems" class="th__center"
                                     :th-ordered="orders.length > 0">
                                Criado em
                            </ThTable>
                            <ThTable th-id="updated_at" @order="orderItems" class="th__center"
                                     :th-ordered="orders.length > 0">
                                Última atualização
                            </ThTable>
                            <ThTable th-id="id" :th-ordered="false" class="th__center">Ações</ThTable>
                        </tr>
                        </thead>
                        <TableLoading v-if="loading" :count-columns="8" :count-rows="10" :count-word="2"/>
                        <Transition name="fade">
                            <tbody v-if="!loading">
                            <tr v-if="orders.length === 0">
                                <td colspan="8" class="row not--found">Sem registros encontrados, adicione um nova
                                    saída.
                                </td>
                            </tr>
                            <tr class="row" v-for="order in orders" :key="order.id">
                                <td class="cursor__pointer adjustment__text" @click="handleUpdateOrder(order)">
                                    {{ order.id }}
                                </td>
                                <td class="cursor__pointer adjustment__text" @click="handleUpdateOrder(order)">
                                    {{ useFormatName(order.user?.name) ?? '' }}
                                </td>
                                <td class="cursor__pointer adjustment__text" @click="handleUpdateOrder(order)">
                                    {{ useFormatName(order.requester_user?.name) ?? '' }}
                                </td>
                                <td class="row__item cursor__pointer text__center">
                                    <select
                                        :class="['form__input', 'status__' + order.status]"
                                        name="status" id="status"
                                        :disabled="disableOptions(order)"
                                        @change="(event) => handleUpdateStatus(event, order)"
                                    >
                                        <option v-for="(opt, index) in showOptions(order)"
                                                :key="index" :value="opt"
                                                :selected="order.status === opt"
                                        >{{ useTranslateText(opt, 'pt') }}
                                        </option>
                                    </select>
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateOrder(order)">
                                    {{ order.order_items_count }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateOrder(order)">
                                    {{ useFormatDate(order.created_at, 'table') }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateOrder(order)">
                                    {{ useFormatDate(order.updated_at, 'table') }}
                                </td>
                                <td class="action__button">
                                    <div class="dropdown">
                                        <Dropdown button-label="Ações" :opened="stateDropdown"
                                                  @click="this.stateDropdown = !this.stateDropdown">
                                            <DropdownLink @click="handleCloneOrder(order)">Clonar
                                            </DropdownLink>
                                            <template v-if="false">
                                                <DropdownLink @click="handleExportPDF(order)">Baixar PDF
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </Transition>
                    </table>
                </div>
                <div class="content__footer">
                    <Pagination @to-page="changePage" :pagination="pagination"/>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import "/resources/css/pages/order.css";
</style>
