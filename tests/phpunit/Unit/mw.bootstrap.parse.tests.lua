--[[
	Tests for mw.bootstrap.parse module

	@since 1.1

	@licence GNU GPL v3+
	@author Tobias Oetterer
]]

local testframework = require 'Module:TestFramework'

local removeId = function( result )
	local ret = string.gsub( result, 'id="[^"]+"', '' )
	ret = string.gsub( ret, '  ', ' ' )
	return ret
end

-- Tests
local tests = {
	{
		name = 'mw.bootstrap.parse.tests.lua: parse (nil argument)',
		func = mw.bootstrap.parse,
		args = { nil },
		expect = { 'No component name provided for mw.bootstrap.parse.' }
	},
	{
		name = 'mw.bootstrap.parse.tests.lua: parse (no argument)',
		func = mw.bootstrap.parse,
		args = { '' },
		expect = { 'No component name provided for mw.bootstrap.parse.' }
	},
	{
		name = 'mw.bootstrap.parse.tests.lua: parse with invalid component',
		func = mw.bootstrap.parse,
		args = { 'foobar', '',  {} },
		expect = { 'Invalid component name passed to mw.bootstrap.parse: foobar.' }
	},
	{
		name = 'mw.bootstrap.parse.tests.lua: parse icon',
		func = mw.bootstrap.parse,
		args = { 'icon', 'asterisk', {} },
		expect = { '<span class="glyphicon glyphicon-asterisk"></span>' }
	},
	{
		name = 'mw.bootstrap.parse.tests.lua: parse alert with arguments',
		func = function( component, input, args )
			return removeId ( mw.bootstrap.parse( component, input, args ) )
		end,
		args = { 'alert', 'Alert content', { color = 'success', dismissible = 'fade' } },
		expect = { '<div class="alert alert-success fade in" role="alert"><div type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></div>Alert content</div>' }
	},
}

return testframework.getTestProvider( tests )
