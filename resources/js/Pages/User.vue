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
import RegisterUser from "@/Components/User/RegisterUser.vue";
import UpdateUser from "@/Components/User/UpdateUser.vue";
import TableUserLoading from "@/Components/User/TableUserLoading.vue";
import useFormatName from "../Composables/useFormatName.js";

export default {
    name: "User",
    components: {
        TableUserLoading,
        UpdateUser,
        RegisterUser,
        TrashButton,
        SearchBar,
        ModalConfirm,
        Dropdown, DropdownLink, Pagination, ThTable, ButtonSecondary, ButtonPrimary, Head
    },
    methods: {
        useFormatName,
        useTranslateText,
        async getUsers() {
            //inicia carregamento da tabela
            this.loading = true;
            try {
                //requisição para recuperar os users
                const response = await axios.get(this.toRoute, {
                    params: {
                        per_page: this.perPage,
                        order_by: this.orderBy,
                        order_direction: this.orderDirection,
                        filters: this.filters
                    }
                });

                //atualiza users
                this.users = response.data.data.data;

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
                this.getUsers();
            } else {
                this.orderBy = 'created_at';
                this.orderDirection = 'desc';
                this.getUsers();
            }
        },
        //navegando entre as páginas de fato
        async changePage(url) {
            this.toRoute = url;
            await this.getUsers();
        },
        handleUpdateUser(user) {
            //abrindo modal com user selecionado
            this.user = user;
            this.updateUserShow = true;
        },
        handleDeleteUser(user) {
            //deletando 1 item pelo dropdown link
            this.user = user;
            this.deleteUserShow = true;
        },
        clearAllFilters() {
            const filters = this.filters;
            Object.keys(filters).forEach(key => {
                filters[key] = null;
            });
        },
        async deleteProduct() {//função para requisição de deletar itens
            try {
                const endpointRoute = '/' + this.authUser.level + '/user/delete/' + this.user.id;
                const response = await axios.delete(endpointRoute);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });

                if (this.users.length === 1) {//se for o último user da página atual
                    this.toRoute = this.defaultRoute;
                }

                this.getUsers();
                this.deleteUserShow = false;
            } catch
                (errors) {
                this.errors = errors.response.data.message;
                this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
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
            await this.getUsers();
        },
        async clearFilterButton() {
            this.clearAllFilters()
            document.getElementById('search-input').value = '';
            await this.getUsers();
        }
    },
    async mounted() {//carragamento de componentes
        this.toast = useSwalAlert({}, 1500);
        await this.getUsers();
    },
    data() {
        return {
            users: [],
            user: {},
            pagination: {},
            toRoute: this.authUser.level + '/user/get',
            registerUserShow: false,
            updateUserShow: false,
            deleteUserShow: false,
            stateDropdown: true,
            loading: false,
            filters: {},
            orderBy: 'updated_at',
            orderDirection: 'desc',
            perPage: 20,
            defaultRoute: this.authUser.level + '/user/get'
        }
    },
    props: {
        authUser: {
            type: Object,
            required: true
        }
    }
}
</script>

<template>
    <div class="content__content">
        <Head name="Users"/>
        <ModalConfirm
            item="usuário"
            :additional-information="'Usuário \'' + user.name + '\' selecionado.'"
            :visible="deleteUserShow"
            @delete="deleteProduct"
            @close="deleteUserShow = false"
        />
        <RegisterUser
            :visible="registerUserShow"
            :auth-user="authUser"
            @close="registerUserShow = false"
            @new-User="getUsers"
        />
        <UpdateUser
            :user="user"
            :visible="updateUserShow"
            :auth-user="authUser"
            @close="updateUserShow = false"
            @update-user="getUsers"
        />
        <div class="content">
            <div class="content__header">
                <ButtonPrimary
                    name="Novo Usuário"
                    @click="registerUserShow = true"
                />
            </div>
            <div class="content__main">
                <div class="main__search">
                    <SearchBar @search="handleSearch"/>
                    <TrashButton @click="clearFilterButton"/>
                </div>
                <div class="main__abstract">
                </div>
                <div class="main__table">
                    <table class="table">
                        <thead class="table__thead">
                        <tr>
                            <ThTable th-id="registration" @order="orderItems">
                                Matrícula
                            </ThTable>
                            <ThTable th-id="name" @order="orderItems">
                                Nome
                            </ThTable>
                            <ThTable th-id="email" @order="orderItems" class="th__center">
                                Email
                            </ThTable>
                            <ThTable th-id="status" @order="orderItems" class="th__center">
                                Status
                            </ThTable>
                            <ThTable th-id="sector" @order="orderItems" class="th__center">
                                Setor
                            </ThTable>
                            <ThTable th-id="level" @order="orderItems" class="th__center">
                                Nível
                            </ThTable>
                            <ThTable v-if="authUser.level === 'admin'" th-id="id" :th-ordered="false" class="th__center">
                                Ações
                            </ThTable>
                        </tr>
                        </thead>
                        <TableUserLoading v-if="loading" :auth-user="authUser"/>
                        <Transition name="fade">
                            <tbody v-if="!loading">
                            <tr v-if="users.length === 0">
                                <td colspan="9" class="row not--found">Sem registros encontrados, adicione um novo usuário.</td>
                            </tr>
                            <tr class="row" v-for="user in users" :key="user.id">
                                <td class="cursor__pointer adjustment__text text__center" @click="handleUpdateUser(user)">
                                    {{ user.registration }}
                                </td>
                                <td class="cursor__pointer" @click="handleUpdateUser(user)">
                                    {{ useFormatName(user.name) }}
                                </td>
                                <td class="cursor__pointer" @click="handleUpdateUser(user)">
                                    {{ user.email }}
                                </td>
                                <td class="cursor__pointer text__center field__status" @click="handleUpdateUser(user)">
                                    <p :class="[user.status === 'active' ? 'status--active' : 'status--inactive']">
                                        {{ user.status === 'active' ? 'Ativo' : 'Inativo' }}
                                    </p>
                                </td>
                                <td class="cursor__pointer" @click="handleUpdateUser(user)">
                                    {{ user.sector }}
                                </td>
                                <td class="cursor__pointer" @click="handleUpdateUser(user)">
                                    {{ useTranslateText(user.level) }}
                                </td>
                                <td v-if="authUser.level === 'admin'" class="action__button">
                                    <div class="dropdown">
                                        <Dropdown button-label="Ações" :opened="stateDropdown"
                                                  @click="this.stateDropdown = !this.stateDropdown">
                                            <DropdownLink @click="handleDeleteUser(user)">Excluir
                                            </DropdownLink>
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
@import "/resources/css/pages/user.css";
</style>
