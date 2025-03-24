<template>
    <div class="content">
        <Head title="Login"/>

        <div class="content__logo">
            <img class="content__logo--img" src="/assets/img/logo.jpg" alt="logo da empresa rce">
        </div>
        <h1 class="content__title">Informe seus dados abaixo</h1>

        <div class="content__form">
            <form action="/login" method="post" id="form-login">
                <div class="inputs">
                    <div class="input__item">
                        <label class="form__label" for="email">E-mail<span class="field-required">*</span></label>
                        <input class="form__input"
                               @keydown.enter="loginSubmit"
                               @keydown="removeStyleInputError"
                               type="email" id="email" name="email" placeholder="Informe o e-mail" required/>
                    </div>
                    <div class="input__item">
                        <label class="form__label" for="password">Senha<span class="field-required">*</span></label>
                        <input class="form__input"
                               @keydown.enter="loginSubmit"
                               @keydown="removeStyleInputError"
                               type="password" id="password"
                               name="password" placeholder="Informe a senha" required/>
                    </div>
                    <Anchor @click.prevent.native="showForgotPassword" class="anchor__forgot"
                            name="Esqueci minha senha"/>
                </div>
            </form>
        </div>

        <ButtonPrimary @click="loginSubmit" meuId="submit" class="button__specify" name="Entrar"/>

        <Anchor @click.prevent.native="showRegister" class="anchor__register"
                name="Não tem uma conta? Exeperimente grátis!"/>
    </div>
</template>

<script>
import axios from 'axios';
import Anchor from "@/Components/Anchor.vue";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import {Head} from "@inertiajs/vue3";

export default {
    name: "Login",
    components: {Head, ButtonPrimary, Anchor},
    mounted() {
        this.toast = useSwalAlert({}, 2000)
    },
    methods: {
        async loginSubmit() {
            try {
                const formLogin = document.getElementById("form-login");
                const formData = new FormData(formLogin);

                const response = await axios.post('/login', {
                    email: formData.get('email'),
                    password: formData.get('password'),
                });
                await this.toast.fire({
                    icon: 'success',
                    title: response.data.message,
                    timer: 500
                });
                window.location.href = response.data.redirect;
            } catch (errors) {
                if (errors.response.status === 422) {
                    const inputsIds = errors.response.data.errors;
                    await useFormatInputError(inputsIds)
                }

                this.errors = errors.response.data.message;
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
        showRegister() {
            this.$emit('show-register');
        },
        showForgotPassword() {
            this.$emit('show-forgot');
        }
    },
    data() {
        return {
            errors: [],
            toast: null
        }
    }, emits: ['show-register', 'show-forgot'],
}
</script>

<style scoped>
@import "/resources/css/components/login.css";

</style>
