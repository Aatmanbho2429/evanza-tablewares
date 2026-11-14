# Putting the new Evanza site on Hostinger

This guide is for the person who runs the website. You do not need to write code. Allow about an hour. The old site stays safe until you decide to switch, and you can go back to it in a few clicks.

What you need first:
- Your Hostinger login and the WordPress admin login for evanzatablewares.com.
- The theme file `evanza-theme.zip` (made with `npm run package`, or sent to you).
- Your real catalogue as a PDF, if you have one.

---

## 1. Back up the current site

Do this before anything else.

1. Log in to Hostinger, open your website, and go to **Backups**. Click **Generate backup** (or check that today's automatic backup exists).
2. Optional extra copy: in WordPress, install the plugin **UpdraftPlus**, open **Settings > UpdraftPlus Backups** and click **Backup now**. Download the files when it finishes.

If anything goes wrong later, this backup is how you get the old site back (see step 10).

## 2. Check the address format (permalinks)

1. In WordPress, go to **Settings > Permalinks**.
2. Choose **Post name** (it looks like `/sample-post/`) and click **Save Changes**.

The new site needs this so that pages like `/collection/` and `/about/` work.

## 3. Upload and activate the theme

1. Go to **Appearance > Themes > Add New Theme > Upload Theme**.
2. Choose `evanza-theme.zip` and click **Install Now**. Wait for it to finish.
3. Click **Activate**.

When you activate it, the site sets itself up:
- It creates the pages Home, About, Gallery and Contact and sets Home as the front page.
- It creates the menus.
- It adds 20 sample mugs ("demo products") in 8 categories so the Collection page is not empty.
- It sets the site tagline to "Ceramic mugs for every table."

Open your site in a new tab and look around. Pages you already had (for example an old "Contact" page) keep their own content; the theme uses its own layouts for About, Gallery and Contact.

## 4. Set your contact details (Customizer)

1. Go to **Appearance > Customize > Evanza enquiries**.
2. **WhatsApp number**: country code first, digits only (for example `919898798986`). The "Enquire on WhatsApp" buttons use it.
3. **Enquiry email**: where enquiries should arrive.
4. **Catalogue PDF**: click **Select file** and upload your catalogue. This replaces the placeholder behind every "Download catalogue" button.
5. Click **Publish**.

## 5. Make the contact form email work

Web hosts often block or lose the emails WordPress sends. Every enquiry is also saved inside WordPress (see **Enquiries** in the left menu), so nothing is lost, but you want the email too.

1. Go to **Plugins > Add New** and install **WP Mail SMTP** (or **FluentSMTP**).
2. Follow its setup and connect your Gmail or your Hostinger email account.
3. Send the test email from the plugin's settings page.
4. Then send a test enquiry from the Contact page and check that it arrives.

## 6. Add your real mugs

1. Go to **Products > Add new product**.
2. Type the **mug name**. Write the description in the big box.
3. In **Mug details** choose the shape and fill in capacity, material, finish and minimum order. Tick "Featured" to show it first, and the badges that apply.
4. On the right, tick the **category**. (A mug with no category only shows when nobody is filtering.)
5. In **Colours** click **Add a colour** for each colour:
   - Colour name, for example "Midnight Navy".
   - Colour family. This is what the colour filter on the Collection page uses.
   - **Outside**, **Inside** and **Handle** colours. Click a colour box to pick one.
   - **Photos** (optional). Click **Add photos** and choose real photos of that colour. Drag them to change the order.
   - **Tile picture** (optional). If you leave it empty, the site draws a clean picture of the mug from your colours, the same style as the samples. Add a transparent PNG only if you want your own picture.
6. Click **Publish**.

The mug appears on the Home page lists, the Collection page (with its colour dots and filters) and its own page, with the 3D mug in your colours.

**Categories:** go to **Products > Categories**. For each category you can add a **Catalogue PDF** (the "Download catalogue" button shows when someone filters to that category) and edit the description.

**Changing the order:** on a product, use "Order" on the right. Lower numbers come first.

## 7. Remove the sample mugs

When your own mugs are in:

1. Go to **Tools > Remove demo products**.
2. Click **Remove demo products** and confirm. Only the sample mugs are removed. Yours are never touched.

(In the Products list, the samples are marked "Demo".)

## 8. Final checks before you tell anyone

- Visit Home, Collection (try the colour dots and filters), a product, About, Gallery and Contact, on your phone and on a computer.
- Click **Enquire on WhatsApp** on a product and check the message and number.
- Add a mug to the enquiry list (button at the top right) and send it by WhatsApp.
- Send a test enquiry from the Contact page and check it arrives by email and under **Enquiries**.
- Check the footer details (phone numbers, email and address).

## 9. Go live

If you installed the theme on the live site, it is already live as soon as you activated it. If you are trying it on a copy first, use Hostinger's **Staging** or **Clone** feature, check everything there, then push the staging site to the live site.

If you use a caching plugin, clear its cache (Settings of that plugin > Clear cache). If the contact form ever says "This page has expired", clear the cache: the form needs a fresh copy of the page.

## 10. Go back to the old site if you need to

Quick way: **Appearance > Themes**, activate your old theme again. The new site's pages and products stay in WordPress, but visitors see the old design.

Full way: in Hostinger, open **Backups** and restore the backup from step 1.

---

## If something looks wrong

| Problem | What to do |
|---|---|
| A page shows "Not found" | Go to **Settings > Permalinks** and click **Save Changes** once. |
| The 3D mug does not show | Reload the page. Very old browsers cannot show 3D; they see a flat picture of the mug instead. |
| Enquiry emails do not arrive | Finish step 5. Meanwhile read them under **Enquiries** in the WordPress menu. |
| A product does not show in a colour filter | Open it and check the **Colour family** of each colour. |
| Old content still shows | Clear any caching plugin and the Hostinger cache. |
