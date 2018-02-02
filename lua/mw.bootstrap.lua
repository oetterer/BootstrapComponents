--[[
	Registers methods that can be accessed through the Scribunto extension

	@since 1.0

	@licence GNU GPL v3
	@author Tobias Oetterer
]]

-- Variable instantiation
local bootstrap = {}
local php

function bootstrap.setupInterface()
	-- Interface setup
	bootstrap.setupInterface = nil
	php = mw_interface
	mw_interface = nil

	-- Register library within the "mw.smw" namespace
	mw = mw or {}
	mw.bootstrap = bootstrap

	package.loaded['mw.bootstrap'] = bootstrap
end

-- parse
function bootstrap.parse( component, input, arguments )
	return php.parse( component, input, arguments )
end

-- getSkin
function bootstrap.getSkin()
	return php.getSkin()
end

return bootstrap
