{{block name=content}}
	<Layout pageTitle={`${title} | ${SITE_NAME}`} pageDescription={SITE_DESC} pageAuthor={SITE_AUTHOR} appName={SITE_NAME}
		appFullName={SITE_NAME} pageLogo="/favicon.svg">
		<!-- Navbar -->
		<Navbar user={user} level={level} />
		<Image id="background" src={background} alt="Site Image Background" fetchpriority="high" />
		<div class="flex
		overflow-hidden
		text-primary-800
        dark:text-white
        bg-gradient-to-b
		from-primary-100/50 to-primary-400/50
        dark:from-primary-500/70 dark:to-primary-800/70">
			<div id="main-content" class="relative
			w-full
			h-full
			min-h-screen
			max-w-screen-2xl
			mx-auto
			overflow-y-auto">
				<!-- BREADCRUMBS -->
				{hasBreadcrumbs && <Breadcrumbs currentRoute={currentPath} routes={breadcrumbs} headerTitle={title} />}
				<div class="px-4 pt-16 2x1:px-0 rounded-md">
					<slot />
				</div>
				<!-- Footer -->
				<Footer />
			</div>
		</div>
</Layout>
{{/block}}
