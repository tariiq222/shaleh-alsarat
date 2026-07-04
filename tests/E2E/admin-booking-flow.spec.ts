import { test, expect, type Page } from '@playwright/test';

/**
 * Chalet MVP — End-to-end admin flow.
 *
 * Required environment:
 *   - The dev DB must be seeded before running (`php artisan migrate:fresh --seed`)
 *   - `ADMIN_EMAIL` and `ADMIN_PASSWORD` env vars must match the seeded admin user.
 *
 * Selector notes:
 *   - Admin forms use React/Inertia with controlled inputs (no `name` attribute,
 *     only `id`). We select by `#id` or by accessible label via getByLabel().
 *   - The public Blade inquiry form has native `name` attributes (selectable by name).
 *   - FullCalendar renders events as `.fc-event` with title inside `.fc-event-title`.
 */
test.describe('Chalet MVP — End-to-end admin flow', () => {
    const adminEmail = process.env.ADMIN_EMAIL ?? 'admin@chalet.local';
    const adminPassword = process.env.ADMIN_PASSWORD ?? 'password';

    /**
     * Log in to the admin SPA via the /login page.
     */
    async function loginAsAdmin(page: Page) {
        await page.goto('/login');
        await page.locator('#email').fill(adminEmail);
        await page.locator('#password').fill(adminPassword);
        await page.getByRole('button', { name: 'تسجيل الدخول' }).click();
        await expect(page).toHaveURL(/\/admin/);
    }

    /**
     * Critical flow: admin login + create booking + add payment + see in calendar.
     * These four sub-steps are the MUST-PASS core of the MVP.
     */
    test('admin login + create booking + add payment + see in calendar', async ({ page }) => {
        // 1. Admin login
        await loginAsAdmin(page);

        // 2. Create a booking via /admin/bookings/create
        await page.goto('/admin/bookings/create');

        const today = new Date();
        const startDate = today.toISOString().slice(0, 10);
        const endDate = new Date(today.getTime() + 2 * 86400000)
            .toISOString()
            .slice(0, 10);

        await page.locator('#customer_name').fill('محمد العلي');
        await page.locator('#customer_phone').fill('+966501234567');
        await page.locator('#start_date').fill(startDate);
        await page.locator('#end_date').fill(endDate);
        await page.locator('#total_amount').fill('1500');
        await page.getByRole('button', { name: 'حفظ الحجز' }).click();

        await expect(page).toHaveURL(/\/admin\/bookings\/\d+/);

        // 3. Add a payment on the booking show page
        await page.locator('#amount').fill('500');
        await page.getByRole('button', { name: 'تسجيل الدفعة' }).click();

        // After submit, the booking show page should reflect the recorded amount
        await expect(page.getByText('500').first()).toBeVisible();

        // 4. See the booking on the calendar
        await page.goto('/admin/calendar');
        await expect(page.locator('.fc-event').first()).toContainText(
            'محمد العلي',
        );
    });

    /**
     * Public inquiry submission from the homepage.
     */
    test('public user submits inquiry from homepage', async ({ page }) => {
        const today = new Date();
        const preferredDate = new Date(today.getTime() + 7 * 86400000)
            .toISOString()
            .slice(0, 10);

        await page.goto('/');
        await page.locator('input[name="name"]').fill('سارة');
        await page.locator('input[name="phone"]').fill('+966507654321');
        await page.locator('input[name="preferred_date"]').fill(preferredDate);
        await page.locator('textarea[name="message"]').fill('أرغب بحجز ليلة');
        await page.getByRole('button', { name: 'إرسال الطلب' }).click();

        await expect(
            page.getByText('تم استلام طلبك').first(),
        ).toBeVisible();
    });

    /**
     * Admin converts an inquiry to a booking.
     *
     * NOTE: This relies on the shadcn Radix DropdownMenu in
     * resources/js/Pages/Admin/Inquiries/Index.tsx. The dropdown is opened by
     * clicking the row's actions trigger (aria-label="إجراءات"), then the
     * "تحويل إلى حجز" link inside the menu. If the Radix portal delays differ
     * between environments, adjust by using `force: true` or extend the wait.
     */
    test('admin converts inquiry to booking', async ({ page }) => {
        // Seed an inquiry via the public form (the public page is reachable
        // without auth). We do this first so the admin sees it in the list.
        await page.goto('/');
        await page.locator('input[name="name"]').fill('نورة للتحويل');
        await page.locator('input[name="phone"]').fill('+966502233445');
        await page.locator('input[name="message"]').fill('طلب تحويل');
        await page.getByRole('button', { name: 'إرسال الطلب' }).click();
        await expect(
            page.getByText('تم استلام طلبك').first(),
        ).toBeVisible();

        // Now log in as admin
        await loginAsAdmin(page);

        await page.goto('/admin/inquiries');

        // Open the first row's actions menu and click "تحويل إلى حجز"
        await page.getByRole('button', { name: 'إجراءات' }).first().click();
        await page.getByRole('link', { name: 'تحويل إلى حجز' }).first().click();

        await expect(page).toHaveURL(/\/admin\/inquiries\/\d+\/convert/);

        // The convert page prefills name/phone/dates from the inquiry; we just
        // need to add the total_amount and submit.
        await page.locator('#total_amount').fill('2000');
        await page.getByRole('button', { name: 'إنشاء الحجز' }).click();

        await expect(page).toHaveURL(/\/admin\/bookings\/\d+/);
    });
});