<button {{ $attributes->merge(['type' => 'submit', 'class' => 'crm-button-primary']) }}>
    {{ $slot }}
</button>
