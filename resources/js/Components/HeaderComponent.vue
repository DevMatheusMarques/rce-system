<script>

import FullScreenModal from "@/Components/FullScreenModal.vue";
import Anchor from "@/Components/Anchor.vue";
import ModalRight from "@/Components/ModalRight.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import UpdateEmail from "@/Components/User/UpdateEmail.vue";
import UpdatePassword from "@/Components/User/UpdatePassword.vue";
import Profile from "@/Components/User/Profile.vue";
import useFormatName from "@/Composables/useFormatName.js";

export default {
    name: "HeaderComponent",
    components: {Profile, UpdatePassword, UpdateEmail, ButtonPrimary, ModalRight, Anchor, FullScreenModal},
    props: {
        authUser: {
            required: true,
        }
    },
    mounted() {
        this.toast = useSwalAlert({}, 2000);
        this.handleFirstAccessUser();
    },
    computed: {
        nameFormatted() {
            const name = this.authUser.name;
            if (name) {
                return useFormatName(name)
            }
            return '';
        },
        urlProfilePicture() {
            const picturePath = this.authUser.profile_picture_path;
            return picturePath ? picturePath : '/assets/img/avatar-example.jpg';
        }
    },
    data() {
        return {
            showModalProfile: false,
            showModalPassword: false,
            showModalEmail: false,
            preloadProfileUrl: '',
        };
    },
    emits: ['profileUpdated', 'userUpdated'],
    methods: {
        handlePictureUpdated(authUser) {
            this.$emit('profileUpdated', authUser);
        },
        handleFirstAccessUser() { //força a troca de senha do usuário no primeiro acesso (sómente quando o usuário é criado)
            if (!this.authUser.first_access) {
                return
            }
            this.toast.fire({
                icon: 'warning',
                title: 'Expiração de senha',
                text: 'Sua senha expirará em breve, realize a troca',
                timer: 4000
            });
            setTimeout(() => {
                this.showModalProfile = true;
                setTimeout(() => {
                    this.showModalPassword = true;
                }, 1000);
            }, 1000);
        }
    }
}
</script>

<template>
    <Profile
        :visible="showModalProfile"
        @close="showModalProfile = false"
        @open-email="showModalEmail = true"
        @open-password="showModalPassword = true"
        @profile-updated="handlePictureUpdated"
        :auth-user="authUser"
        :url-profile-picture="urlProfilePicture"
    />
    <UpdatePassword
        :visible="showModalPassword"
        @close="showModalPassword = false"
        :auth-user="authUser"
        @user-updated="this.$emit('userUpdated')"
    />
    <UpdateEmail
        :visible="showModalEmail"
        @close="showModalEmail = false"
        :auth-user="authUser"
    />
    <header class="header">
        <div class="header__item header__logo">
            <img class="header__logo--img" src="/assets/img/logotipo.png" alt="Logo da empresa rce">
        </div>
        <div class="header__item header__profile">
            <div @click="showModalProfile = true" class="header__profile--info border__link">
                <div class="header__img">
                    <img class="header__profile--img" :src="urlProfilePicture" alt="avatar do usuário autenticado">
                    <div class="access__level" :class="['access__level--' + authUser.level]"></div>
                </div>
                <span class="header__profile--name">{{ nameFormatted }}</span>
            </div>
        </div>
    </header>
</template>
<style scoped>
@import "/resources/css/components/header-component.css";
</style>
