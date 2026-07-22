<template>
    <div>
        <Head title="Instagram"/>

        <Heading class="mb-6">
            Instagram Settings
        </Heading>

        <Card class="overflow-hidden">
            <div
                class="p-6 flex justify-between items-center"
            >
                <div>
                    <h2 class="text-lg font-semibold">
                        Instagram Account
                    </h2>

                    <p
                        class="mt-1"
                        :class="account ? 'text-green-500' : 'text-red-500'"
                    >
                        {{
                            account ?
                                'No Instagram account connected.'
                                :
                                'Connected account information.'
                        }}
                    </p>
                </div>

                <a
                    v-if="!account"
                    href="/nova-vendor/instagram/auth"
                    class="
                        inline-flex
                        items-center
                        px-4
                        py-2
                        rounded
                        text-sm
                        font-semibold
                        bg-primary-500
                        text-white
                        hover:bg-primary-600
                    "
                >
                    Add Account
                </a>
            </div>

            <div
                v-if="account !== null"
                class="overflow-x-auto"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="px-6 py-3 text-sm font-semibold">
                                Username
                            </th>

                            <th class="px-6 py-3 text-sm font-semibold">
                                Name
                            </th>

                            <th class="px-6 py-3 text-sm font-semibold">
                                Account ID
                            </th>

                            <th class="px-6 py-3 text-sm font-semibold">
                                Access Token
                            </th>

                            <th class="px-6 py-3 text-sm font-semibold">
                                Added account at
                            </th>

                            <th class="px-6 py-3 text-sm font-semibold">
                                Expired Date
                            </th>

                            <th class="px-6 py-3 text-sm font-semibold">
                                Action
                            </th>
                        </tr>
                    </thead>


                    <tbody>
                        <tr class="border-t">
                            <td class="px-6 py-4">
                                {{ account.username }}
                            </td>

                            <td class="px-6 py-4">
                                {{ account.name ?? '–' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ account.instagram_id }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <input
                                        readonly
                                        class="form-control form-input form-input-bordered w-72"
                                        :value="
                                            showToken
                                                ? account.access_token
                                                : maskedToken
                                        "
                                    >

                                    <button
                                        class="
                                            inline-flex
                                            items-center
                                            px-3
                                            py-2
                                            rounded
                                            text-sm
                                            font-semibold
                                            bg-primary-500
                                            text-white
                                        "
                                        @click="showToken = !showToken"
                                    >
                                        {{ showToken ? 'Hide' : 'Show' }}
                                    </button>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                {{ formatDate(account.created_at) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ formatDate(account.token_expires_at) }}
                            </td>

                            <td class="px-6 py-4">
                                <button
                                    type="button"
                                    class="
                                        inline-flex
                                        items-center
                                        px-3
                                        py-2
                                        rounded
                                        text-sm
                                        font-semibold
                                        bg-red-500
                                        text-white
                                    "
                                    @click="removeAccount"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>


<script>
export default {
    data() {
        return {
            showToken: false,
            account: null,
        }
    },
    async mounted() {
        const accountData = await Nova.request()
            .get('/nova-vendor/instagram/getData')
            .then(response => response.data?.data);

        this.account = accountData ?? null;
    },
    computed: {
        maskedToken() {
            if (!this.account) {
                return;
            }

            const token = this.account.access_token;

            return token.substring(0, 3)
                + '****************'
                + token.substring(token.length - 3);
        }
    },
    methods: {
        formatDate(date) {
            if (!date) {
                return '-';
            }

            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();

            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');

            return `${day}.${month}.${year} ${hours}:${minutes}`;
        },
        async removeAccount() {
            if (!window.confirm('Are you sure you want to delete Instagram account?')) {
                return;
            }

            try {
                await Nova.request()
                    .delete('/nova-vendor/instagram/delete');

                this.account = null;

                Nova.success('Instagram account removed');
            } catch (error) {
                console.log(error);

                Nova.error('Failed to remove Instagram account');
            }
        }
    }
}
</script>
