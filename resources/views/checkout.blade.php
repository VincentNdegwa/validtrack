

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Checkout</title>
    @vite(['resources/js/app.ts'])
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @paddleJS
</head>
<body class="font-sans antialiased" >
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-lg mx-auto rounded-xl shadow-lg p-8 border bg-card border-border">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold mb-2 text-foreground">Checkout</h1>
                <p class="text-muted-foreground">Complete your subscription below.</p>
            </div>

            <!-- Plan Details -->
            @if (isset($plan))
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-3 w-3 rounded-full bg-primary"></span>
                            <span class="font-semibold text-lg text-foreground">{{ $plan->name }}</span>
                        </div>
                        @if(isset($billing_cycle))
                            <span class="px-2 py-1 rounded text-xs font-medium bg-secondary text-secondary-foreground">{{ ucfirst($billing_cycle) }}</span>
                        @endif
                    </div>
                    <div class="text-sm mb-2 text-muted-foreground">{{ $plan->description }}</div>
                    @if(isset($billing_cycle) && $billing_cycle === 'monthly')
                        <div class="flex items-center justify-between py-2">
                            <span class="text-muted-foreground">Billing Cycle</span>
                            <span class="font-bold text-lg text-primary">Monthly</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-muted-foreground">Price</span>
                            <span class="font-bold text-lg text-primary">{{ $plan->monthly_price ? number_format($plan->monthly_price, 2) : '-' }}</span>
                        </div>
                    @elseif(isset($billing_cycle) && $billing_cycle === 'yearly')
                        <div class="flex items-center justify-between py-2">
                            <span class="text-muted-foreground">Billing Cycle</span>
                            <span class="font-bold text-lg text-primary">Yearly</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-muted-foreground">Price</span>
                            <span class="font-bold text-lg text-primary">{{ $plan->yearly_price ? number_format($plan->yearly_price, 2) : '-' }}</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between py-2">
                            <span class="text-muted-foreground">Monthly Price</span>
                            <span class="font-bold text-lg text-primary">{{ $plan->monthly_price ? number_format($plan->monthly_price, 2) : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-muted-foreground">Yearly Price</span>
                            <span class="font-bold text-lg text-primary">{{ $plan->yearly_price ? number_format($plan->yearly_price, 2) : '-' }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Paddle Button -->
            @if ($checkout != null)
                <div class="mt-6">
                    <x-paddle-button :checkout="$checkout"
                        class="w-full font-semibold py-3 px-6 rounded-lg shadow transition-all duration-200 flex items-center justify-center bg-primary text-primary-foreground hover:bg-accent hover:text-accent-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        Subscribe Now
                    </x-paddle-button>
                </div>
            @endif

            <div class="mt-8 text-center text-xs text-muted-foreground">
                By subscribing, you agree to our <a href="#" class="text-accent hover:underline">Terms</a> and <a href="#" class="text-accent hover:underline">Privacy Policy</a>.
            </div>
        </div>
    </div>
</body>
</html>
