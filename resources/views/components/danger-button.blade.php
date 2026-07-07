<button {{ $attributes->merge(['type' => 'submit', 'class' => 'crm-button-danger']) }}>
    {{ $slot }}
</button>
