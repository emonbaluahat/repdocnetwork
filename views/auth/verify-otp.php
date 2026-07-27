<div x-data="otpHandler()">
    <form method="POST" action="<?= url('verify-otp') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label for="phone" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                ফোন নম্বর
            </label>
            <input type="tel" id="phone" name="phone" x-model="phone"
                   class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                   placeholder="017xxxxxxxx" required>
        </div>

        <div>
            <label for="otp" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                ওটিপি কোড
            </label>
            <input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                   x-model="otp"
                   class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm text-center text-2xl tracking-widest"
                   placeholder="______" required>
        </div>

        <button type="submit"
                class="w-full py-2 px-4 bg-primary-700 dark:bg-primary-600 hover:bg-primary-800 dark:hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition font-bengali">
            নিশ্চিত করুন
        </button>

        <div class="text-center">
            <p x-show="!sending && !sent" class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">
                ওটিপি পাননি?
                <button @click="sendOtp()" class="text-primary-700 dark:text-primary-400 hover:underline font-bengali">
                    পুনরায় পাঠান
                </button>
            </p>
            <p x-show="sending" class="text-sm text-text-tertiary dark:text-text-dark-tertiary font-bengali">
                ওটিপি পাঠানো হচ্ছে...
            </p>
            <p x-show="sent && !sending" class="text-sm text-success-600 dark:text-success-400 font-bengali">
                ওটিপি পাঠানো হয়েছে। <span x-text="countdown"></span> সেকেন্ড পর পুনরায় চেষ্টা করুন।
            </p>
        </div>
    </form>
</div>

<script>
function otpHandler() {
    return {
        phone: '',
        otp: '',
        sending: false,
        sent: false,
        countdown: 0,
        timer: null,
        sendOtp() {
            if (this.sending || this.phone.length < 10) return;
            this.sending = true;
            fetch('<?= url("send-otp") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: '_csrf_token=' + encodeURIComponent('<?= csrf_token() ?>') + '&login=' + encodeURIComponent(this.phone)
            })
            .then(r => r.json())
            .then(data => {
                this.sending = false;
                if (data.error) {
                    alert(data.error);
                } else {
                    this.sent = true;
                    this.countdown = 30;
                    this.timer = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            this.sent = false;
                            clearInterval(this.timer);
                        }
                    }, 1000);
                }
            })
            .catch(() => { this.sending = false; });
        }
    };
}
</script>
