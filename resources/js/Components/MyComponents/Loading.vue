<template>
    <!-- <div class="flex flex-col items-center text-secondary">
        <i class="fa-solid fa-square fa-bounce text-4xl"></i>
        <span class="text-[10px]">Cargando...</span>
    </div> -->
    <div class="container">
        <div class="item item-1 mx-1 my-1"></div>
        <div class="item item-2 mx-1 my-1"></div>
        <div class="item item-3 mx-1 my-1"></div>
        <div class="item item-4 mx-1 my-1"></div>
    </div>
</template>
<script>
export default {

}
</script>
<style scoped>
/* From Uiverse.io by fanishah */
.container {
    position: absolute;
    width: 80px;
    height: 80px;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: auto;
}

.item {
    width: 30px;
    height: 30px;
    position: absolute;
}

.item-1 {
    background-color: #25346D;
    top: 0;
    left: 0;
    z-index: 1;
    animation: item-1_move 1.8s cubic-bezier(.6, .01, .4, 1) infinite;
}

.item-2 {
    background-color: #25346D;
    top: 0;
    right: 0;
    animation: item-2_move 1.8s cubic-bezier(.6, .01, .4, 1) infinite;
}

.item-3 {
    background-color: #25346D;
    bottom: 0;
    right: 0;
    z-index: 1;
    animation: item-3_move 1.8s cubic-bezier(.6, .01, .4, 1) infinite;
}

.item-4 {
    background-color: #D72C8A;
    bottom: 0;
    left: 0;
    animation: item-4_move 1.8s cubic-bezier(.6, .01, .4, 1) infinite;
}

@keyframes item-1_move {

    0%,
    100% {
        transform: translate(0, 0)
    }

    25% {
        transform: translate(0, 40px)
    }

    50% {
        transform: translate(40px, 40px)
    }

    75% {
        transform: translate(40px, 0)
    }
}

@keyframes item-2_move {

    0%,
    100% {
        transform: translate(0, 0)
    }

    25% {
        transform: translate(-40px, 0)
    }

    50% {
        transform: translate(-40px, 40px)
    }

    75% {
        transform: translate(0, 40px)
    }
}

@keyframes item-3_move {

    0%,
    100% {
        transform: translate(0, 0)
    }

    25% {
        transform: translate(0, -40px)
    }

    50% {
        transform: translate(-40px, -40px)
    }

    75% {
        transform: translate(-40px, 0)
    }
}

@keyframes item-4_move {

    0%,
    100% {
        transform: translate(0, 0)
    }

    25% {
        transform: translate(40px, 0)
    }

    50% {
        transform: translate(40px, -40px)
    }

    75% {
        transform: translate(0, -40px)
    }
}
</style>