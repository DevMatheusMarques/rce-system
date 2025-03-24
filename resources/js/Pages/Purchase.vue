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
import useFormatDate from "@/Composables/useFormatDate.js";
import TableLoading from "@/Components/TableLoading.vue";
import {defineAsyncComponent} from "vue";


export default {
    name: "Purchase",
    components: {
        TableLoading,
        RegisterPurchase: defineAsyncComponent(() => import('@/Components/Purchases/RegisterPurchase.vue')),
        UpdatePurchase: defineAsyncComponent(() => import('@/Components/Purchases/UpdatePurchase.vue')),
        ClonePurchase: defineAsyncComponent(() => import('@/Components/Purchases/ClonePurchase.vue')),
        TrashButton,
        SearchBar,
        ModalConfirm,
        Dropdown, DropdownLink, Pagination, ThTable, ButtonSecondary, ButtonPrimary, Head
    },
    methods: {
        useTranslateText,
        useFormatDate,
        async getPurchases() {
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
                this.purchases = response.data.data.data;
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
                this.getPurchases();
            } else {
                this.orderBy = 'created_at';
                this.orderDirection = 'desc';
                this.getPurchases();
            }
        },
        async changePage(url) {
            this.toRoute = url;
            await this.getPurchases();
        },
        handleUpdatePurchase(purchase) {
            //abrindo modal com pedido de compra selecionado
            this.purchase = purchase;
            this.updatePurchaseShow = true;
        },
        clearAllFilters() {
            const filters = this.filters;
            Object.keys(filters).forEach(key => {
                filters[key] = null;
            });
        },
        handleExportPDF(purchase) {
            window.location.href = this.authUser.level + '/purchase/export/pdf/' + purchase.id;
            this.toast.fire({
                icon: 'success',
                title: 'PDF gerado, logo será baixado.'
            });
        },
        handleClonePurchase(purchase) {
            this.purchase = purchase;
            this.clonePurchaseShow = true;
        },
        async handleSearch(val) {
            if (val === '') {//verifica se um item foi selecionado
                return this.toast.fire({
                    icon: 'warning',
                    title: 'Insira algum valor para continuar.',
                });
            }
            this.clearAllFilters();
            this.filters.search = useTranslateText(val, 'en');
            this.toRoute = this.defaultRoute;
            await this.getPurchases();
        },
        async handleFilterDate(event) {
            this.filters.created_at = event.target.value;
            await this.getPurchases();
        },
        async clearFilterButton() {
            this.clearAllFilters()
            document.getElementById('search-input').value = '';
            document.querySelector('.form__input-date').value = '';
            await this.getPurchases();
        },
        async updateStatus(data) {
            try {
                const endpointRoute = '/' + this.authUser.level + '/purchase/status/update';
                const response = await axios.put(endpointRoute, data);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                this.getPurchases();
            } catch (errors) {
                data.element.value = data.status_old;
                this.errors = errors.response.data.message;
                this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        handleUpdateStatus(event, purchase) {
            const data = {
                id: purchase.id,
                status: event.target.value,
                status_old: purchase.status,
                element: event.target
            }
            this.updateStatus(data);
        },
        showOptions(purchase) {
            switch (purchase.status) {
                case 'approved':
                    if (this.isOperator) {
                        return ['in_progress', 'canceled', 'approved'];
                    }
                    return ['approved', 'refused', 'in_progress', 'canceled'];
                case 'refused':
                    if (this.isOperator) {
                        return ['canceled', 'refused'];
                    }
                    return ['refused', 'approved', 'canceled'];
                case 'in_progress':
                    return ['in_progress', 'completed', 'canceled'];

                case 'completed':
                    return ['completed'];
                case 'canceled':
                    return ['canceled'];
            }
        },
        disableOptions(purchase) {
            return purchase.status === 'canceled' || purchase.status === 'completed';
        },
    },
    async mounted() {//carragamento de componentes
        this.toast = useSwalAlert({}, 1500);
        await this.getPurchases();
    },
    data() {
        return {
            purchases: {},
            purchase: {},
            pagination: {},
            toRoute: this.authUser.level + '/purchase/get',
            registerPurchaseShow: false,
            updatePurchaseShow: false,
            clonePurchaseShow: false,
            stateDropdown: true,
            loading: false,
            filters: {},
            orderBy: 'id',
            orderDirection: 'desc',
            perPage: 20,
            defaultRoute: this.authUser.level + '/purchase/get'
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
        <Head name="Suppliers"/>
        <component
            :is="'RegisterPurchase'"
            :v-if="registerPurchaseShow"
            :visible="registerPurchaseShow"
            :auth-user="authUser"
            @close="registerPurchaseShow = false"
            @new-purchase="getPurchases"
        />
        <component
            :is="'UpdatePurchase'"
            :v-if="updatePurchaseShow"
            :purchase="purchase"
            :visible="updatePurchaseShow"
            :auth-user="authUser"
            @close="updatePurchaseShow = false"
            @update-purchase="getPurchases"
        />
        <component
            :is="'ClonePurchase'"
            v-if="clonePurchaseShow"
            :purchase="purchase"
            :visible="clonePurchaseShow"
            :auth-user="authUser"
            @close="clonePurchaseShow = false"
            @new-purchase="getPurchases"
        />
        <div class="content">
            <div class="content__header">
                <ButtonPrimary
                    name="Nova Compra"
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
                            <ThTable th-id="id" @order="orderItems" :th-ordered="purchases.length > 0">
                                ID
                            </ThTable>
                            <ThTable th-id="user_name" @order="orderItems" :th-ordered="purchases.length > 0">
                                Responsável
                            </ThTable>
                            <ThTable th-id="supplier_cnpj" @order="orderItems" :th-ordered="purchases.length > 0">
                                Fornecedor
                            </ThTable>
                            <ThTable th-id="status" @order="orderItems" class="th__center" :th-ordered="purchases.length > 0">
                                Status
                            </ThTable>
                            <ThTable th-id="items_count" @order="orderItems" class="th__center" :th-ordered="false">
                                Quantidade itens
                            </ThTable>
                            <ThTable th-id="created_at" @order="orderItems" class="th__center" :th-ordered="purchases.length > 0">
                                Criado em
                            </ThTable>
                            <ThTable th-id="updated_at" @order="orderItems" class="th__center" :th-ordered="purchases.length > 0">
                                Última atualização
                            </ThTable>
                            <ThTable th-id="id" :th-ordered="false" class="th__center">Ações</ThTable>
                        </tr>
                        </thead>
                        <TableLoading v-if="loading" :count-columns="8" :count-rows="10" :count-word="2"/>
                        <Transition name="fade">                            <tbody v-if="!loading">
                            <tr v-if="purchases.length === 0">
                                <td colspan="9" class="row not--found">Sem registros encontrados, adicione um novo
                                    pedido de compra.
                                </td>
                            </tr>
                            <tr class="row" v-for="purchase in purchases" :key="purchase.id">
                                <td class="cursor__pointer adjustment__text" @click="handleUpdatePurchase(purchase)">
                                    {{ purchase.id }}
                                </td>
                                <td class="cursor__pointer adjustment__text" @click="handleUpdatePurchase(purchase)">
                                    {{ purchase.user?.name ?? '' }}
                                </td>
                                <td class="cursor__pointer" @click="handleUpdatePurchase(purchase)">
                                    {{ purchase.supplier?.cnpj ?? '' }}
                                </td>
                                <td class="row__item cursor__pointer text__center">
                                    <select
                                        :class="['form__input', 'status__' + purchase.status]"
                                        name="status" id="status"
                                        :disabled="disableOptions(purchase)"
                                        @change="(event) => handleUpdateStatus(event, purchase)"
                                    >
                                        <option v-for="(opt, index) in showOptions(purchase)"
                                                :key="index" :value="opt"
                                                :selected="purchase.status === opt"
                                        >{{ useTranslateText(opt, 'pt') }}
                                        </option>
                                    </select>
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdatePurchase(purchase)">
                                    {{ purchase.purchase_items_count }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdatePurchase(purchase)">
                                    {{ useFormatDate(purchase.created_at, 'table') }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdatePurchase(purchase)">
                                    {{ useFormatDate(purchase.updated_at, 'table') }}
                                </td>
                                <td class="action__button">
                                    <div class="dropdown">
                                        <Dropdown button-label="Ações" :opened="stateDropdown"
                                                  @click="this.stateDropdown = !this.stateDropdown">
                                            <DropdownLink @click="handleClonePurchase(purchase)">Clonar
                                            </DropdownLink>
                                            <template v-if="purchase.status !== 'refused'">
                                                <DropdownLink @click="handleExportPDF(purchase)">Baixar PDF
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
@import "/resources/css/pages/purchase.css";
</style>
