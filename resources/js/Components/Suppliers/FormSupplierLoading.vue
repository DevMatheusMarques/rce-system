<script>
export default {
    name: "FormSupplierLoading",
}
</script>

<template>
    <div class="input__item">
        <label for="phone" class="form__label">Telefone</label>
        <input
            type="tel" class="form__input loading__background" name="phone" id="phone"
        >
    </div>
    <div class="input__item">
        <label for="email" class="form__label">Email</label>
        <input
            type="email" class="form__input loading__background" name="email" id="email"
        >
    </div>
    <div class="input__item">
        <label for="corporate_name" class="form__label">Razão Social</label>
        <input
            type="text" class="form__input loading__background" name="corporate_name" id="corporate_name"
        >
    </div>
    <div class="input__item">
        <label for="trade_name" class="form__label">Nome Fantasia</label>
        <input
            type="text" class="form__input loading__background" name="trade_name" id="trade_name"
        >
    </div>
    <div class="input__item">
        <label for="cep" class="form__label">CEP</label>
        <input
            type="tel" class="form__input loading__background" name="cep" id="cep"
        >
    </div>
    <div class="input__item address">
        <div>
            <label for="address_city" class="form__label">Cidade</label>
            <input
                type="text" class="form__input loading__background" name="address_city" id="address_city"
            >
        </div>
        <div>
            <label for="address_state" class="form__label">Estado</label>
            <input
                type="text" class="form__input loading__background" name="address_state" id="address_state"
            >
        </div>
    </div>
</template>

<style scoped>
.address {
    display: flex;
    gap: 1rem;
    justify-content: space-around;
}

@keyframes placeHolderShimmer {
    0% {
        background-position: -468px 0;
    }
    100% {
        background-position: 468px 0;
    }
}

.loading__background {
    animation-duration: 1.25s;
    animation-fill-mode: forwards;
    animation-iteration-count: infinite;
    animation-name: placeHolderShimmer;
    animation-timing-function: linear;
    background: linear-gradient(to right, var(--gray-light-hover) 10%, var(--gray-light) 18%, var(--gray-light-hover) 33%);
    background-size: 800px 104px;
    color: transparent;
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
