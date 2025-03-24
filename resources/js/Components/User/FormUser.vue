<script>
import {mask} from "vue-the-mask";
import SearchBar from "@/Components/SearchBar.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useSectorArray from "@/Composables/useSectorArray.js";

export default {
    name: "FormUser",
    components: {SearchBar},
    directives: {mask},
    props: {
        preventEnterAction: {
            type: Function,
            required: true
        },
        userProp: {
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
        this.user = this.userProp;
        this.toast = useSwalAlert({}, 2000);
        document.querySelector(".modal__body").classList.add('custom__modal-body--user');
    },
    data() {
        return {
            user: {},
            formLoading: false,
            arraySector: useSectorArray(),
        }
    },
    methods: {
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        }
    }, computed: {
        userStatus() {
            return this.userProp.status || null;
        },
        userSector() {
            return this.userProp.sector || null;
        },
        userLevel() {
            return this.userProp.level || null;
        },
        canEdit() {
            if (this.authUser.level === 'admin') {
                return true;
            }
            if (this.authUser.level === 'manager' && this.userProp.level !== 'admin') {
                return true;
            }
            return false;
        }
    }
}
</script>

<template>
    <div class="form__user">
        <form :id="formId">
            <div class="form__content" v-if="!formLoading">
                <div class="form__col">
                    <div class="input__item">
                        <label for="name" class="form__label">Nome</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="text" class="form__input" name="name" id="name"
                               :value="user.name ?? ''"
                               :disabled="!canEdit"
                        >
                    </div>
                    <div class="input__item">
                        <label for="email" class="form__label">Email</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="email" class="form__input" name="email" id="email"
                               :value="user.email ?? ''"
                               :disabled="!canEdit"
                        >
                    </div>
                    <div class="input__item">
                        <label for="phone" class="form__label">Telefone</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="tel" class="form__input" name="phone" id="phone"
                               :value="user.phone ?? ''"
                               v-mask="['(##) ####-####', '(##) #####-####']"
                               :disabled="!canEdit"
                        >
                    </div>
                    <div class="input__item">
                        <label for="status" class="form__label">Status</label>
                        <select class="form__input" name="status" id="status"
                                @change="removeStyleInputError"
                                @keydown.prevent.enter="preventEnterAction"
                                v-model="userStatus"
                                :disabled="!canEdit"
                        >
                            <option value="active">Ativo</option>
                            <option value="inactive">Inativo</option>
                        </select>
                    </div>
                    <div class="input__item">
                        <label for="level" class="form__label">Nível Acesso</label>
                        <select class="form__input" name="level" id="level"
                                @change="removeStyleInputError"
                                @keydown.prevent.enter="preventEnterAction"
                                v-model="userLevel"
                                :disabled="!canEdit"
                        >
                            <option value="operator">Operador</option>
                            <option value="manager">Gerente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="input__item function">
                        <div>
                            <label for="registration" class="form__label">Matrícula</label>
                            <input @keydown="removeStyleInputError"
                                   @keydown.prevent.enter="preventEnterAction"
                                   type="tel" class="form__input" name="registration" id="registration"
                                   :value="user.registration ?? ''"
                                   :disabled="!canEdit"

                            >
                        </div>
                        <div>
                            <label for="sector" class="form__label">Setor</label>
                            <select class="form__input" name="sector" id="sector"
                                    @change="removeStyleInputError"
                                    @keydown.prevent.enter="preventEnterAction"
                                    v-model="userSector"
                                    :disabled="!canEdit"
                            >
                                <option v-for="opt in arraySector" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
<style scoped>
.function {
    display: flex;
    gap: 1rem;
    justify-content: space-around;
}
.form__user {
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
