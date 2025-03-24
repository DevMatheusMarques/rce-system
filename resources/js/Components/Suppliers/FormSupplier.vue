<script>
import InputImage from "@/Components/InputImage.vue";
import {mask} from "vue-the-mask";
import axios from "axios";
import FormSupplierLoading from "@/Components/Suppliers/FormSupplierLoading.vue";
import SearchBar from "@/Components/SearchBar.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";

export default {
    name: "FormSupplier",
    components: {SearchBar, FormSupplierLoading, InputImage},
    directives: {mask},
    props: {
        preventEnterAction: {
            type: Function,
            required: true
        },
        supplierProp: {
            type: Object,
            default: () => ({}),
            required: false
        },
        formId: {
            type: String,
            required: true
        },
        authUser: {
            type: Object,
            default: () => ({}),
        }
    },
    mounted() {
        this.supplier = this.supplierProp;
        this.toast = useSwalAlert({}, 2000);
        document.querySelector(".modal__body").classList.add('custom__modal--body');
    },
    data() {
        return {
            supplier: {},
            formLoading: false,
            valueCnpj: ''
        }
    },
    methods: {
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        },
        onlyNumbers(str) {
            return parseInt(str.replace(/\D/g, ''));
        },
        async getDataByCnpj(val) {
            try {
                if (val === '') {//verifica se um item foi selecionado
                    return this.toast.fire({
                        icon: 'warning',
                        title: 'Insira algum valor para continuar.',
                    });
                }
                this.valueCnpj = val;
                this.formLoading = true;
                const cnpj = this.onlyNumbers(val)
                const response = await axios.get('/consult/' + cnpj);
                const data = response.data;

                this.supplier = {
                    cnpj: cnpj,
                    corporate_name: data.razao_social,
                    trade_name: data.estabelecimento.nome_fantasia,
                    email: data.estabelecimento.email,
                    cep: data.estabelecimento.cep,
                    phone: data.estabelecimento.ddd1 + data.estabelecimento.telefone1,
                    address_city: data.estabelecimento.cidade.nome,
                    address_state: data.estabelecimento.estado.nome,
                }
            } catch (errors) {
                console.error(errors)
                this.supplier = this.supplierProp;

                this.toast.fire({
                    icon: 'error',
                    title: 'CNPJ não encontrado'
                });
            } finally {
                this.formLoading = false;
            }
        },
        async getDataByCnpjOld(val) {
            try {
                this.valueCnpj = val;
                this.formLoading = true;
                const cnpj = this.onlyNumbers(val)
                const response = await axios.get('/proxy/buscarcnpj', {
                    params: {
                        cnpj: cnpj
                    }
                });
                const data = response.data;
                if (data.error) {
                    this.supplier = {
                        cnpj: '',
                        corporate_name: '',
                        trade_name: '',
                        email: '',
                        cep: '',
                        phone: '',
                        address_city: '',
                        address_state: '',
                    }
                    throw new Error(data.error);
                }

                this.supplier = {
                    cnpj: data.CNPJ,
                    corporate_name: data["RAZAO SOCIAL"],
                    trade_name: data["NOME FANTASIA"],
                    email: data.EMAIL,
                    cep: data.CEP,
                    phone: data.DDD + data.TELEFONE,
                    address_city: data.MUNICIPIO,
                    address_state: data.UF,
                }
            } catch (errors) {
                console.error(errors)
                this.toast.fire({
                    icon: 'error',
                    title: 'CNPJ não encontrado'
                });
            } finally {
                this.formLoading = false;
            }
        }
    }
}
</script>

<template>
    <div class="form__supplier">
        <form :id="formId">
            <div class="form__content">
                <div class="form__col">
                    <div class="input__item">
                        <label for="cnpj" class="form__label">CNPJ</label>
                        <SearchBar
                            placeholder=""
                            @search="getDataByCnpj"
                            custom-id="cnpj"
                            custom-name="cnpj"
                            custom-type="tel"
                            :custom-value="supplier.cnpj === '' ? valueCnpj : supplier.cnpj"
                            v-mask="'##.###.###/####-##'"
                        />
                    </div>
                    <FormSupplierLoading v-if="formLoading"/>
                    <div class="input__item" v-if="!formLoading">
                        <label for="phone" class="form__label">Telefone</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="tel" class="form__input" name="phone" id="phone"
                               :value="supplier.phone ?? ''"
                               v-mask="['(##) ####-####', '(##) #####-####']"
                        >
                    </div>
                    <div class="input__item" v-if="!formLoading">
                        <label for="email" class="form__label">Email</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="email" class="form__input" name="email" id="email"
                               :value="supplier.email ?? ''"
                        >
                    </div>
                    <div class="input__item" v-if="!formLoading">
                        <label for="corporate_name" class="form__label">Razão Social</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="text" class="form__input" name="corporate_name" id="corporate_name"
                               :value="supplier.corporate_name ?? ''"
                        >
                    </div>
                    <div class="input__item" v-if="!formLoading">
                        <label for="trade_name" class="form__label">Nome Fantasia</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="text" class="form__input" name="trade_name" id="trade_name"
                               :value="supplier.trade_name ?? ''"
                        >
                    </div>
                    <div class="input__item" v-if="!formLoading">
                        <label for="cep" class="form__label">CEP</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="tel" class="form__input" name="cep" id="cep"
                               :value="supplier.cep ?? ''"
                               v-mask="'#####-###'"
                        >
                    </div>
                    <div class="input__item address" v-if="!formLoading">
                        <div>
                            <label for="address_city" class="form__label">Cidade</label>
                            <input @keydown="removeStyleInputError"
                                   @keydown.prevent.enter="preventEnterAction"
                                   type="text" class="form__input" name="address_city" id="address_city"
                                   :value="supplier.address_city ?? ''"
                            >
                        </div>
                        <div>
                            <label for="address_state" class="form__label">Estado</label>
                            <input @keydown="removeStyleInputError"
                                   @keydown.prevent.enter="preventEnterAction"
                                   type="text" class="form__input" name="address_state" id="address_state"
                                   :value="supplier.address_state ?? ''"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
<style scoped>
.address {
    display: flex;
    gap: 1rem;
    justify-content: space-around;
}
.form__supplier {
    width: 100%;
}

.form__content {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    gap: 4rem;
}

.form__col {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
</style>
