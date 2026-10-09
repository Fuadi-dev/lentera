<script setup lang="ts">
import type { SliderRootEmits, SliderRootProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { SliderRange, SliderRoot, SliderThumb, SliderTrack, useForwardPropsEmits } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<SliderRootProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<SliderRootEmits>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <SliderRoot
        v-slot="{ modelValue }"
        data-slot="accessibility-slider"
        data-reka-swipe-ignore
        :class="
            cn(
                'relative flex w-full touch-none items-center select-none data-disabled:opacity-50 data-[orientation=vertical]:min-h-40 data-[orientation=vertical]:h-full data-[orientation=vertical]:w-auto data-[orientation=vertical]:flex-col',
                props.class
            )
        "
        v-bind="forwarded"
        @pointerdown.stop
        @pointermove.stop
        @pointerup.stop
        @touchstart.stop
        @touchmove.stop
        @touchend.stop
    >
        <SliderTrack
            data-slot="slider-track"
            class="bg-muted relative grow overflow-hidden rounded-full data-[orientation=horizontal]:h-1.5 data-[orientation=horizontal]:w-full data-[orientation=vertical]:h-full data-[orientation=vertical]:w-1.5"
        >
            <SliderRange
                data-slot="slider-range"
                class="bg-primary absolute select-none data-[orientation=horizontal]:h-full data-[orientation=vertical]:w-full"
            />
        </SliderTrack>

        <SliderThumb
            v-for="(_, key) in modelValue"
            :key="key"
            data-slot="slider-thumb"
            class="border-primary ring-ring/50 size-4 rounded-full border bg-white shadow-sm transition-[color,box-shadow] hover:ring-4 focus-visible:ring-4 focus-visible:outline-hidden block shrink-0 select-none disabled:pointer-events-none disabled:opacity-50"
        />
    </SliderRoot>
</template>
