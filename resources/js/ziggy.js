const Ziggy = {
	"url": "http:\/\/localhost:8000", "port": 8000, "defaults": {}, "routes": {
		"boost.browser-logs": {"uri": "_boost\/browser-logs", "methods": ["POST"]},
		"cashier.payment": {"uri": "stripe\/payment\/{id}", "methods": ["GET", "HEAD"], "parameters": ["id"]},
		"cashier.webhook": {"uri": "stripe\/webhook", "methods": ["POST"]},
		"welcome": {"uri": "\/", "methods": ["GET", "HEAD"]},
		"register": {"uri": "auth\/register", "methods": ["GET", "HEAD"]},
		"register.store": {"uri": "auth\/register", "methods": ["POST"]},
		"login": {"uri": "auth\/login", "methods": ["GET", "HEAD"]},
		"login.store": {"uri": "auth\/login", "methods": ["POST"]},
		"logout": {"uri": "auth\/logout", "methods": ["POST"]},
		"password.request": {"uri": "auth\/forgot-password", "methods": ["GET", "HEAD"]},
		"password.email": {"uri": "auth\/forgot-password", "methods": ["POST"]},
		"password.reset": {"uri": "auth\/reset-password\/{token}", "methods": ["GET", "HEAD"], "parameters": ["token"]},
		"password.update": {"uri": "auth\/reset-password", "methods": ["POST"]},
		"verification.notice": {"uri": "email\/verify", "methods": ["GET", "HEAD"]},
		"verification.verify": {
			"uri": "verify-email\/{id}\/{hash}", "methods": ["GET", "HEAD"], "parameters": ["id", "hash"]
		},
		"verification.send": {"uri": "email\/verification-notification", "methods": ["POST"]},
		"dashboard": {"uri": "dashboard", "methods": ["GET", "HEAD"]},
		"admin.dashboard": {"uri": "admin", "methods": ["GET", "HEAD"]},
		"budgets.index": {"uri": "budgets", "methods": ["GET", "HEAD"]},
		"budgets.create": {"uri": "budgets\/create", "methods": ["GET", "HEAD"]},
		"budgets.store": {"uri": "budgets", "methods": ["POST"]},
		"budgets.show": {
			"uri": "budgets\/{budget}",
			"methods": ["GET", "HEAD"],
			"parameters": ["budget"],
			"bindings": {"budget": "id"}
		},
		"budgets.edit": {
			"uri": "budgets\/{budget}\/edit",
			"methods": ["GET", "HEAD"],
			"parameters": ["budget"],
			"bindings": {"budget": "id"}
		},
		"budgets.update": {
			"uri": "budgets\/{budget}",
			"methods": ["PUT", "PATCH"],
			"parameters": ["budget"],
			"bindings": {"budget": "id"}
		},
		"budgets.destroy": {
			"uri": "budgets\/{budget}", "methods": ["DELETE"], "parameters": ["budget"], "bindings": {"budget": "id"}
		},
		"budgets.expenses.store": {
			"uri": "budgets\/{budget}\/expenses",
			"methods": ["POST"],
			"parameters": ["budget"],
			"bindings": {"budget": "id"}
		},
		"expenses.update": {
			"uri": "expenses\/{expense}", "methods": ["PUT"], "parameters": ["expense"], "bindings": {"expense": "id"}
		},
		"expenses.destroy": {
			"uri": "expenses\/{expense}",
			"methods": ["DELETE"],
			"parameters": ["expense"],
			"bindings": {"expense": "id"}
		},
		"subscription.checkout": {"uri": "subscription-checkout\/{plan}", "methods": ["POST"], "parameters": ["plan"]},
		"subscription.manage": {"uri": "subscription", "methods": ["GET", "HEAD"]},
		"plans": {"uri": "plans", "methods": ["GET", "HEAD"]},
		"subscription.swap": {
			"uri": "subscription\/swap\/{plan}",
			"methods": ["POST"],
			"wheres": {"plan": "monthly|yearly"},
			"parameters": ["plan"]
		},
		"subscription.cancel": {"uri": "subscription\/cancel", "methods": ["POST"]},
		"subscription.resume": {"uri": "subscription\/resume", "methods": ["POST"]},
		"billing": {"uri": "billing", "methods": ["GET", "HEAD"]},
		"billing.success": {"uri": "billing\/success", "methods": ["GET", "HEAD"]},
		"billing.cancel": {"uri": "billing\/cancel", "methods": ["GET", "HEAD"]},
		"budgets.chat": {
			"uri": "budgets\/{budget}\/chat",
			"methods": ["POST"],
			"parameters": ["budget"],
			"bindings": {"budget": "id"}
		},
		"budgets.scan-ticket": {
			"uri": "budgets\/{budget}\/scan-ticket",
			"methods": ["POST"],
			"parameters": ["budget"],
			"bindings": {"budget": "id"}
		},
		"settings.profile": {"uri": "settings\/profile", "methods": ["GET", "HEAD"]},
		"settings.profile.update": {"uri": "settings\/profile", "methods": ["PUT"]},
		"settings.password": {"uri": "settings\/password", "methods": ["GET", "HEAD"]},
		"settings.password.update": {"uri": "settings\/password", "methods": ["PUT"]},
		"storage.local": {
			"uri": "storage\/{path}", "methods": ["GET", "HEAD"], "wheres": {"path": ".*"}, "parameters": ["path"]
		},
		"storage.local.upload": {
			"uri": "storage\/{path}", "methods": ["PUT"], "wheres": {"path": ".*"}, "parameters": ["path"]
		}
	}
};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
	Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export {Ziggy};
