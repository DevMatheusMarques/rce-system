<template>
    <div class="content">
        <Head title="Register"/>
        <div class="content__logo">
            <img class="content__logo--img" src="/assets/img/logo.jpg" alt="logo da empresa rce">
        </div>
        <h1 class="content__title">Cadastre-se</h1>

        <div class="content__form">
            <form action="/login" method="post" id="form-login" class="form">
                <div class="input__item">
                    <label class="form__label" for="name">Nome<span class="field-required">*</span></label>
                    <input class="form__input"
                           @keydown.enter="registerSubmit"
                           @keydown="removeStyleInputError"
                           type="text" id="name" name="name" placeholder="Informe seu nome" required
                           autofocus/>
                </div>
                <div class="input__item">
                    <label class="form__label" for="email">E-mail<span class="field-required">*</span></label>
                    <input class="form__input"
                           @keydown.enter="registerSubmit"
                           @keydown="removeStyleInputError"
                           type="email" id="email" name="email"
                           placeholder="Informe seu e-mail" required/>
                </div>
                <div class="input__item">
                    <label class="form__label" for="phone">Telefone<span class="field-required">*</span></label>
                    <input class="form__input"
                           @keydown.enter="registerSubmit"
                           @keydown="removeStyleInputError"
                           type="tel" id="phone"
                           name="phone" placeholder="Informe seu telefone" required
                           v-mask="['(##) ####-####', '(##) #####-####']"
                    />
                </div>
                <div class="input__item">
                    <label class="form__label" for="password">Senha<span class="field-required">*</span></label>
                    <input class="form__input"
                           @keydown.enter="registerSubmit"
                           @keydown="removeStyleInputError"
                           type="password" id="password"
                           name="password" placeholder="" required/>
                </div>
                <div class="input__item">
                    <label class="form__label" for="password_confirmation">Confirmação Senha<span
                        class="field-required">*</span></label><br>
                    <input class="form__input"
                           @keydown.enter="registerSubmit"
                           @keydown="removeStyleInputError"
                           type="password" id="password_confirmation"
                           name="password_confirmation" placeholder="" required/>
                </div>
            </form>
        </div>

        <ButtonPrimary @click="registerSubmit" meuId="submit" class="button__specify" name="Registrar"/>

        <Anchor @click.prevent.native="showLogin" class="anchor__register" name="Já possui uma conta? Entrar"/>
    </div>
</template>

<script>
import axios from 'axios';
import Anchor from "@/Components/Anchor.vue";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import {Head} from "@inertiajs/vue3";
import {mask} from "vue-the-mask";

export default {
    name: "Register",
    components: {Head, ButtonPrimary, Anchor},
    directives: {mask},
    mounted() {
        this.toast = useSwalAlert(undefined, 10000);
    },
    methods: {
        async registerSubmit() {
            const formLogin = document.getElementById("form-login");
            const formData = new FormData(formLogin);

            try {
                await axios.post('/register', {
                    name: formData.get('name'),
                    email: formData.get('email'),
                    phone: formData.get('phone'),
                    password: formData.get('password'),
                    password_confirmation: formData.get('password_confirmation'),
                });
                this.toast.fire({
                    icon: 'success',
                    title: 'Cadastro realizado!',
                    text: 'Solicite liberação de acesso ao administrador, email: jordandouglas8515@gmail.com',
                });
                this.$emit('show-login');
            } catch (errors) {
                const response = errors.response;
                if (response.status === 422) {
                    const inputsIds = response.data.errors;
                    await useFormatInputError(inputsIds)
                }

                this.errors = response.data.message;
                await this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        },
        showLogin() {
            this.$emit('show-login');
        }
    },
    data() {
        return {
            errors: [],
            toast: null
        }
    }
}
</script>

<style scoped>
@import "/resources/css/components/register.css";
</style>
