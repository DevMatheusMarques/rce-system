<script>
export default {
    name: "Tooltip",
    props: {
        text: {
            type: String,
            required: true
        }
    }
}
</script>

<template>
    <div class="container">
        <div class="tooltip">
            <span class="tooltip__trigger">
                <slot/>
            </span>
            <div class="tooltip__texto">
                <span class="texto__conteudo">
                <img class="bico" src="/assets/icons/bico-tooltip.svg" alt="">
                    <span class="texto__conteudo--text">
                        {{ text }}
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.container {
    display: flex;
    align-items: center;
}

.texto__conteudo {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    top: -14px;
}

.texto__conteudo--text {
    margin-left: -7px;
    background-color: #000000;
    color: #fff;
    text-align: center;
    padding: .3rem .7rem .3rem .75rem;
    border-radius: 6px;
    white-space: nowrap;
}

.bico {
    width: 15px;
    transform: rotate(180deg);
}

.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip__texto {
    width: 90%;
    opacity: 0;
    transition: opacity .4s;
    position: absolute;
    z-index: 3;
    top: 50%;
    left: 100%;
    transform: translateY(-50%);
    margin-left: 2rem;
    pointer-events: none;
}

.tooltip__trigger:hover + .tooltip__texto,
.tooltip__trigger:focus + .tooltip__texto {
    opacity: 1;
}
</style>
